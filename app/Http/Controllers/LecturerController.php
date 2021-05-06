<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Lecturer;
use DateTime;

class LecturerController extends Controller
{
    public function index()
    {
        //
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|max:25',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:20',
            'sex' => 'required|boolean',
            'birthday' => 'required|date',
            'phone_number' => 'min:10|max:12',
            'address' => 'required|max:255',
        ]);

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'status' => false,
                'message' => 'Email sai định dạng'
            ]);
        }

        if (Lecturer::where('email', $request->email)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Email đã tồn tại'
            ]);
        }

        $request->birthday = new DateTime($request->birthday);
        $request->birthday = $request->birthday->format('Y-m-d');
        if (!preg_match("/^(19|20)[0-9]{2}\-(0?[1-9]|1[012])\-(1[0-9]|2[0-9]|3[01]|0?[1-9])$/", $request->birthday)) {
            return response()->json([
                'status' => false,
                'message' => 'Ngày sinh không hợp lệ'
            ]);
        }

        $user = new Lecturer();
        $user->fill($request->all());
        $user->birthday = $request->birthday;

        if (isset($request->phone_number)) {
            $phone_number = preg_replace('/\s+/', '', $request->phone_number);
            if (!preg_match("/^[0-9]{10}$/", $phone_number)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Số điện thoại phải là 10 số (chấp nhận khoảng trắng)'
                ]);
            }

            $user->phone_number = $phone_number;
        }

        $user->password = Hash::make($request->password);
        if (!isset($user->role))
            $user->role = 'gv';

        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Tạo tài khoản thành công',
                'data' => $user
            ], 201);
        }
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Lecturer::where('email', $request->email)->first();
        if ($user) {
            if (!$user->isActive) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản đã bị khóa'
                ]);
            } else {
                if (Hash::check($request->password, $user->password)) {
                    $tokenResult = $user->createToken('Lecturers', [$user->role]);
                    $user->token = $tokenResult->accessToken;
                    // $user->token_expires_at = Carbon::parse($tokenResult->token->expires_at)->toDateTimeString();

                    return response()->json([
                        'status' => true,
                        'message' => 'Đăng nhập thành công',
                        'data' => $user
                    ]);
                }
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Sai email hoặc mật khẩu'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'status' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    public function details()
    {
        return response()->json(auth()->guard('api-lecturer')->user());
    }
}
