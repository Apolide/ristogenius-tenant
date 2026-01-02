<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{


    public function usercreate(){
        return view('users.usercreate');
    }

    public function useredit($id){

        return view('users.useredit', ['id' => $id]);
    }
    
    public function userslist(){
        return view('users.userlist');
    }
}