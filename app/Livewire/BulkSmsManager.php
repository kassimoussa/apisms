<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\BulkSmsJob;
use App\Jobs\ProcessBulkSmsJob;
use Illuminate\Support\Facades\Log;

class BulkSmsManager extends Component
{
    public $client;
    
    // Form fields
    public $name = '';
    public $content = '';
    public $from = '';
    public $recipients = '';
    public $scheduled_at = '';
    
    // Settings
    public $rate_limit = 60;
    public $batch_size = 50;
    
    // UI state
    public $showAdvancedSettings = false;
    public $isSubmitting = false;
    public $successMessage = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'content' => 'required|string|max:1600',
        'from' => 'nullable|string|max:20',
        'recipients' => 'required|string',
        'scheduled_at' => 'nullable|date|after:now',
        'rate_limit' => 'integer|min:1|max:1000',
        'batch_size' => 'integer|min:10|max:500',
    ];

    public function mount()
    {
        $this->client = request()->attributes->get('client');
    }

    public function createCampaign()
    {
        $this->isSubmitting = true;
        $this->validate();

        try {
            // Parse recipients (one per line or comma-separated)
            $recipientsList = collect(preg_split('/[\r\n,;]+/', $this->recipients))
                ->map(fn($r) => trim($r))
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($recipientsList)) {
                $this->addError('recipients', 'Please provide at least one valid recipient.');
                $this->isSubmitting = false;
                return;
            }

            if (count($recipientsList) > 10000) {
                $this->addError('recipients', 'Maximum 10,000 recipients allowed per campaign.');
                $this->isSubmitting = false;
                return;
            }

            // Validate recipient phone numbers
            $validRecipients = [];
            $invalidRecipients = [];

            foreach ($recipientsList as $recipient) {
                $cleanNumber = preg_replace('/[^\d+]/', '', $recipient);
                if (strlen($cleanNumber) >= 8 && strlen($cleanNumber) <= 15) {
                    $validRecipients[] = $cleanNumber;
                } else {
                    $invalidRecipients[] = $recipient;
                }
            }

            if (empty($validRecipients)) {
                $this->addError('recipients', 'No valid phone numbers found. Please check the format.');
                $this->isSubmitting = false;
                return;
            }

            // Create bulk SMS job
            $bulkJob = BulkSmsJob::create([
                'client_id' => $this->client->id,
                'name' => $this->name,
                'content' => $this->content,
                'from' => $this->from ?: null,
                'recipients' => $validRecipients,
                'total_count' => count($validRecipients),
                'pending_count' => count($validRecipients),
                'scheduled_at' => $this->scheduled_at ? now()->parse($this->scheduled_at) : null,
                'settings' => [
                    'rate_limit' => $this->rate_limit,
                    'batch_size' => $this->batch_size,
                ]
            ]);

            // Dispatch job if not scheduled
            if (!$bulkJob->scheduled_at || $bulkJob->scheduled_at->isPast()) {
                ProcessBulkSmsJob::dispatch($bulkJob->id, $this->batch_size);
            }

            Log::info('Bulk SMS campaign created via web interface', [
                'client_id' => $this->client->id,
                'bulk_job_id' => $bulkJob->id,
                'total_recipients' => count($validRecipients),
                'invalid_recipients' => count($invalidRecipients),
            ]);

            // Success message
            $message = "Campaign '{$this->name}' created successfully with {$bulkJob->total_count} recipients!";
            if (count($invalidRecipients) > 0) {
                $message .= " ({" . count($invalidRecipients) . "} invalid numbers were skipped)";
            }
            
            $this->successMessage = $message;
            
            // Reset form
            $this->reset(['name', 'content', 'from', 'recipients', 'scheduled_at']);
            $this->rate_limit = 60;
            $this->batch_size = 50;

        } catch (\Exception $e) {
            Log::error('Failed to create bulk SMS campaign', [
                'client_id' => $this->client->id,
                'error' => $e->getMessage(),
            ]);

            $this->addError('general', 'Failed to create campaign: ' . $e->getMessage());
        }

        $this->isSubmitting = false;
    }

    public function toggleAdvancedSettings()
    {
        $this->showAdvancedSettings = !$this->showAdvancedSettings;
    }

    public function render()
    {
        return view('livewire.bulk-sms-manager')
            ->layout('components.layouts.client');
    }
}