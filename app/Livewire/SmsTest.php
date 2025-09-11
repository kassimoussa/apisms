<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\SmsMessage;
use App\Services\KannelService;

class SmsTest extends Component
{
    public $to = '';
    public $message = '';
    public $client_id = '';
    public $from = '';
    
    public $result = null;
    public $isLoading = false;
    public $kannelStatus = [];

    protected KannelService $kannelService;

    public function boot()
    {
        $this->kannelService = app(KannelService::class);
    }

    public function mount()
    {
        $this->checkKannelStatus();
        $this->client_id = Client::active()->first()?->id;
        $this->message = 'Test SMS from ApiSMS Gateway at ' . now()->format('Y-m-d H:i:s');
    }

    public function checkKannelStatus()
    {
        $this->kannelStatus = $this->kannelService->checkConnectivity();
    }

    public function sendTestSms()
    {
        $this->validate([
            'to' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->kannelService->isValidPhoneNumber($value)) {
                        $fail('Phone number must be valid (+253XXXXXXXX or 77XXXXXX)');
                    }
                },
            ],
            'message' => 'required|string|max:160|min:1',
            'client_id' => 'required|exists:clients,id',
            'from' => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if ($value && !$this->kannelService->isValidSender($value)) {
                        $fail('Sender must be a valid phone number or text (max 11 chars)');
                    }
                },
            ],
        ]);

        $this->isLoading = true;
        $this->result = null;

        try {
            $client = Client::find($this->client_id);
            
            // Create SMS message record
            $smsMessage = SmsMessage::create([
                'client_id' => $client->id,
                'direction' => 'outbound',
                'from' => $this->from ?: config('services.kannel.from'),
                'to' => $this->to,
                'content' => $this->message,
                'status' => 'pending',
                'metadata' => [
                    'test_dashboard' => true,
                    'executed_at' => now()->toISOString(),
                ],
            ]);

            // Send via Kannel
            $kannelResult = $this->kannelService->sendSms(
                $smsMessage->to,
                $smsMessage->content,
                $smsMessage->from
            );

            if ($kannelResult['success']) {
                $smsMessage->markAsSent($kannelResult['kannel_id'] ?? null);
                
                $this->result = [
                    'success' => true,
                    'message' => 'SMS sent successfully!',
                    'sms_id' => $smsMessage->id,
                    'kannel_id' => $kannelResult['kannel_id'],
                    'status' => 'sent',
                ];
            } else {
                $smsMessage->markAsFailed(
                    $kannelResult['error_code'] ?? 'UNKNOWN',
                    $kannelResult['error_message'] ?? 'Unknown error'
                );

                $this->result = [
                    'success' => false,
                    'message' => 'SMS sending failed',
                    'error_code' => $kannelResult['error_code'],
                    'error_message' => $kannelResult['error_message'],
                    'sms_id' => $smsMessage->id,
                ];
            }

        } catch (\Exception $e) {
            $this->result = [
                'success' => false,
                'message' => 'System error occurred',
                'error_message' => $e->getMessage(),
            ];
        } finally {
            $this->isLoading = false;
        }
    }

    public function clearResult()
    {
        $this->result = null;
    }

    public function render()
    {
        return view('livewire.sms-test', [
            'clients' => Client::active()->get()
        ])->layout('components.layouts.app', ['title' => 'SMS Test']);
    }
}
