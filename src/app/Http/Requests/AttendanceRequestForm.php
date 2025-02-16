<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'clock_in_time' => 'date_format:H:i|before:clock_out_time',
            'clock_out_time' => 'date_format:H:i|after:clock_in_time',
            'rests.*.rest_in_time' => 'date_format:H:i',
            'rests.*.rest_out_time' => 'date_format:H:i',
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

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockInTime = $this->input('clock_in_time');
            $clockOutTime = $this->input('clock_out_time');

            if (empty($clockInTime) || empty($clockOutTime)) {
                $validator->errors()->add('clock_in_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間のバリデーション
            $rests = $this->input('rests', []);
            foreach ($rests as $restId => $rest) {
                if (!isset($rest['rest_in_time'], $rest['rest_out_time'])) {
                    $validator->errors()->add("rests.$restId.rest_in_time", '休憩時間が勤務時間外です。');
                    continue;
                }

                if ($rest['rest_in_time'] < $clockInTime || $rest['rest_out_time'] > $clockOutTime) {
                    $validator->errors()->add("rests.$restId.rest_in_time", '休憩時間が勤務時間外です。');
                }
            }
        });
    }
}
