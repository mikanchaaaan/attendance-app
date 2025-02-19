<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class AttendanceRequestForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clock_in_time' => 'required|date_format:H:i|before:clock_out_time',
            'clock_out_time' => 'required|date_format:H:i|after:clock_in_time',

            'rests.*.rest_in_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $clockInTime = $this->input('clock_in_time');
                    $clockOutTime = $this->input('clock_out_time');

                    if ($clockInTime && $clockOutTime) {
                        if ($value < $clockInTime || $value > $clockOutTime) {
                            $fail('休憩時間が勤務時間外です。');
                        }
                    }
                }
            ],

            'rests.*.rest_out_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    $clockInTime = $this->input('clock_in_time');
                    $clockOutTime = $this->input('clock_out_time');

                    if ($clockInTime && $clockOutTime) {
                        if ($value < $clockInTime || $value > $clockOutTime) {
                            $fail('休憩時間が勤務時間外です。');
                        }
                    }
                }
            ],

            'comment' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_in_time.before' => '出勤時間もしくは退勤時間が不適切な値です。',
            'clock_out_time.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'rests.*.rest_in_time.after' => '休憩時間が勤務時間外です。',
            'rests.*.rest_out_time.after' => '休憩時間が勤務時間外です。',
            'rests.*.rest_out_time.before' => '休憩時間が勤務時間外です。',
            'comment.required' => '備考を記入してください。',
        ];
    }
}
