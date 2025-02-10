<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class adminStuffManagementController extends Controller
{
    // スタッフ一覧の表示（管理者用）
    
    public function stuffListView() {
        return view('admin.stuffList');
    }
}
