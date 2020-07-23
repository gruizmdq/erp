<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Role;
use App\User;

class UserController extends Controller
{
    function get_users(Request $request) {
        $role = $request->input('role', null);
        if ($role)
            return Role::where('name', $role)->first()->users;
        
        return User::get();
    }
}
