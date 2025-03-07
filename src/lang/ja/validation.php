<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'required' => ':attributeを入力してください',
    'email'    => ':attribute は有効なメールアドレス形式で入力してください',
    'max'      => [
        'string' => ':attributeはmax文字以内で入力してください',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上で入力してください',
    ],
    'confirmed' => 'パスワードと一致しません',
    'unique'    => '同じ :attribute は使用できません。',
    'date_format' => ':attribute は :format 形式で入力してください。',
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name'                  => 'お名前',
        'email'                 => 'メールアドレス',
        'password'              => 'パスワード',
        'password_confirmation' => 'パスワード確認',
        'clock_in_time' => '出勤時間',
        'clock_out_time' => '退勤時間',
        'rests.*.rest_in_time' => '休憩開始時間',
        'rests.*.rest_out_time' => '休憩終了時間',
    ],

];
