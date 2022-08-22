<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;


class AdminUserlistController extends Controller
{
    //
    public function index(){
        $page_title = __('locale.adminuserlist');
        $page_description = 'Some description for the page';
        $action = 'userlist';
        $result = User::orderBy('id', 'asc')->get()->toArray();
        
        foreach ($result as $key => $value) {
            # code...
            $result[$key]['f_name'] = "joe";
            $result[$key]['l_name'] = "doe";
            $result[$key]['kyc_status'] = "passed";
        }
        return view('zenix.admin.userlist', compact('page_title', 'page_description', 'action', 'result'));
    }
}
