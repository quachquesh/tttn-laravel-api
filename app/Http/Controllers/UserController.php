<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Helpers;
use App\Models\Lecturer;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function details(Request $request)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);
        if ($user) {
            return response()->json($user);
        } else {
            return response()->json([], 404);
        }

    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);
        // Nếu là email thì login lecturer
        if (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $user = Lecturer::where('email', $request->username)->first();
        } else {
            $user = Student::whereIn('mssv', [strtolower($request->username), strtoupper($request->username)])->first();
        }

        if ($user) {
            if (!$user->isActive) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản đã bị khóa'
                ]);
            } else {
                if (Hash::check($request->password, $user->password)) {
                    if ($user->role) {
                        $tokenResult = $user->createToken("Lecturers", [$user->role]);
                    } else {
                        $tokenResult = $user->createToken('Students', ['sv']);
                    }
                    $user->token = $tokenResult->accessToken;
                    // $user->token_expires_at = Carbon::parse($tokenResult->token->expires_at)->toDateTimeString();

                    return response()->json([
                        'status' => true,
                        'message' => 'Đăng nhập thành công',
                        'data' => $user
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Sai Tài khoản hoặc Mật khẩu'
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Sai Tài khoản hoặc Mật khẩu'
            ]);
        }
    }

    public function logout(Request $request)
    {
        $helper = new Helpers;
        $token = $helper->getToken($request);
        if ($token) {
            $token->revoke();
        }
        return response()->json([
            'status' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }
}
