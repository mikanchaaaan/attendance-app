<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use App\Models\User;

class UserRegister extends TestCase
{
    /**
     * A basic feature test example.
     */

    use RefreshDatabase;

    // 認証機能（一般ユーザ） - 名前が未入力の場合
    public function testRegisterName()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $response->assertSessionHas('errors');

        $errors = session('errors')->get('name');
        $this->assertContains('お名前を入力してください', $errors);
    }

    // 認証機能（一般ユーザ）- メールアドレスが未入力の場合
    public function testRegisterEmail()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHas('errors');

        $errors = session('errors')->get('email');
        $this->assertContains('メールアドレスを入力してください', $errors);
    }

    // 認証機能（一般ユーザ） - パスワードが8文字未満の場合
    public function testRegisterPasswordCount()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'word123',
            'password_confirmation' => 'word123',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHas('errors');

        $errors = session('errors')->get('password');
        $this->assertContains('パスワードは8文字以上で入力してください', $errors);
    }

    // 認証機能（一般ユーザ） - パスワードが一致しない場合
    public function testRegisterPasswordConfirm()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password1234',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHas('errors');

        $errors = session('errors')->get('password');
        $this->assertContains('パスワードと一致しません', $errors);
    }

    // 認証機能（一般ユーザ） - パスワードが未入力の場合
    public function testRegisterPassword()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSessionHas('errors');

        $errors = session('errors')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    // 会員登録 - 正常確認
    public function testRegister()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/attendance');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('password123', $user->password));
    }
}
