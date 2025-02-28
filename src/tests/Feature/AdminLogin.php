<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AdminLogin extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    // ログイン認証機能（管理者） - メールアドレス未入力の場合
    public function testLoginEmail()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
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

    // ログイン認証機能（管理者） - パスワード未入力の場合
    public function testLoginPassword()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $errors = session('errors')->get('password');
        $this->assertContains('パスワードを入力してください', $errors);
    }

    // ログイン認証機能（管理者） - 登録内容と一致しない場合
    public function testLoginInvalid()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);

        $user = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->post('login', [
            'email' => 'admin@example.com',
            'password' => 'wrong_password',
        ]);

        $errors = session('errors')->get('email');
        $this->assertContains('ログイン情報が登録されていません', $errors);
    }
}
