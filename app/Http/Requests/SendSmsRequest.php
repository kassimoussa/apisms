<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\KannelService;

class SendSmsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'to' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    $kannelService = new KannelService();
                    if (!$kannelService->isValidPhoneNumber($value)) {
                        $fail('The ' . $attribute . ' must be a valid Djibouti phone number (+253XXXXXXXX).');
                    }
                },
            ],
            'message' => [
                'required',
                'string',
                'max:' . config('services.sms.max_length', 160),
                'min:1',
            ],
            'from' => [
                'nullable',
                'string',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $kannelService = new KannelService();
                        if (!$kannelService->isValidPhoneNumber($value)) {
                            $fail('The ' . $attribute . ' must be a valid Djibouti phone number (+253XXXXXXXX).');
                        }
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'to.required' => 'Recipient phone number is required.',
            'to.max' => 'Phone number is too long.',
            'message.required' => 'SMS message content is required.',
            'message.max' => 'SMS message cannot exceed ' . config('services.sms.max_length', 160) . ' characters.',
            'message.min' => 'SMS message cannot be empty.',
            'from.max' => 'Sender phone number is too long.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'to' => 'recipient number',
            'message' => 'message content',
            'from' => 'sender number',
        ];
    }
}
