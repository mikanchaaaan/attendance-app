<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserLogin extends TestCase
{
    use RefreshDatabase;

    // ログイン認証機能（一般ユーザ） - メールアドレス未入力の場合
    public function testLoginEmail()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $errors = session('errors')->get('email');
        $this->assertContains('メールアドレスを入力してください', $errors);
    }

    // ログイン認証機能（一般ユーザ） - パスワード未入力の場合
    public function testLoginPassword()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('login', [
            'email' => 'test',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    // ログイン認証機能（一般ユーザ） - 登録内容と一致しない場合
    public function testLoginInvalid()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $errors = session('errors')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }
}
