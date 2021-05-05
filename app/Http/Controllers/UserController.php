<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Helpers;

class UserController extends Controller
{
    public function details(Request $request)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        return response()->json($user);
    }
}
