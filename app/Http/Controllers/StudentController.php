<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Student;
use DateTime;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    private $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private function randomPwd()
    {
        return substr(str_shuffle($this->permitted_chars), 0, rand(8, 12));
    }

    private function checkSex($value)
    {
        switch (strtolower($value)) {
            case 'nam':
            case '0':
            case 'male':
                return false;
            default:
                return true;
        }
    }

    public function index()
    {
        return response()->json(Student::all());
    }

    public function store(Request $request)
    {
        if (isset($request->email)) {
            if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email sai định dạng'
                ]);
            }
        }
        $request->validate([
            'mssv' => 'required',
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:20',
            'sex' => 'required|boolean',
            'birthday' => 'required|date',
            'phone_number' => 'min:10|max:12',
            'address' => 'required|max:255'
        ]);

        if (isset($request->phone_number)) {
            $request->phone_number = preg_replace('/\s+/', '', $request->phone_number);
            if (!preg_match("/[0-9]{10}/", $request->phone_number)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Số điện thoại phải là 10 số (chấp nhận khoảng trắng)'
                ]);
            }
        }

        if (Student::where('mssv', $request->mssv)->first()) {
            return response()->json([
                'status' => false,
                'message' => 'Sinh viên đã tồn tại'
            ]);
        }

        $request->birthday = new DateTime($request->birthday);
        $request->birthday = $request->birthday->format('Y-m-d');
        if (!preg_match("/(19|20)[0-9]{2}\-(0?[1-9]|1[012])\-(1[0-9]|2[0-9]|3[01]|0?[1-9])/", $request->birthday)) {
            return response()->json([
                'status' => false,
                'message' => 'Ngày sinh không hợp lệ'
            ]);
        }

        $student = new Student;
        $student->fill($request->all());
        $student->birthday = $request->birthday;
        if (isset($student->password)) {
            $student->password = Hash::make($request->password);
        } else {
            $password_random = $this->randomPwd();
            $student->password = Hash::make($password_random);
        }
        $student->create_by = $request->user()->id;
        if ($student->save()) {
            if (isset($password_random))
                $student->password_random = $password_random;
            return response()->json([
                'status' => true,
                'message' => 'Đăng ký tài khoản thành công',
                'data' => $student
            ], 201);
        }
    }

    // Tạo danh sách account
    public function createList(Request $request)
    {
        $dataTable = $request->dataTable;
        $tableHeader = $request->tableHeader;
        $password_random_flag = false;
        foreach ($dataTable as $key => $value) {
            $check = true;
            $value[$tableHeader['sex'][0]] = $this->checkSex($value[$tableHeader['sex'][0]]);
            if ($value[$tableHeader['sex'][0]] == 0) {
                $dataTable[$key][$tableHeader['sex'][0]] = "Nam";
            } else {
                $dataTable[$key][$tableHeader['sex'][0]] = "Nữ";
            }
            // Nếu có cột email
            if ($tableHeader['email'][0] != null && empty($value[$tableHeader['email'][0]]) == false) {
                if (!filter_var($value[$tableHeader['email'][0]], FILTER_VALIDATE_EMAIL)) {
                    $check = false;
                }
            }
            // Nếu có cột số điện thoại
            if ($tableHeader['phone_number'][0] != null && empty($value[$tableHeader['phone_number'][0]]) == false) {
                $phone_number = preg_replace('/\s+/', '', $value[$tableHeader['phone_number'][0]]);
                $value[$tableHeader['phone_number'][0]] = $phone_number;
                if (preg_match("/[0-9]{10}/", $value[$tableHeader['phone_number'][0]])) {
                    // xxxx xxx xxx
                    $dataTable[$key][$tableHeader['phone_number'][0]] = substr($phone_number, 0, 4) . " " . substr($phone_number, 4, 3) . " " . substr($phone_number, 7, 3);
                } else {
                    $check = false;
                }
            }
            if ($tableHeader['birthday'][0] != null && empty($value[$tableHeader['birthday'][0]]) == false) {
                if (!preg_match("/(19|20)[0-9]{2}\-(0?[1-9]|1[012])\-(1[0-9]|2[0-9]|3[01]|0?[1-9])/", $value[$tableHeader['birthday'][0]])) {
                    $check = false;
                }
            }

            // check requied
            if ($value[$tableHeader['address'][0]] == null || $value[$tableHeader['mssv'][0]] == null || $value[$tableHeader['first_name'][0]] == null || $value[$tableHeader['last_name'][0]] == null) {
                $check = false;
            }

            // Thêm dữ liệu vào database
            if ($check == false) {
                $dataTable[$key]['status'] = false;
            } else {
                if (Student::where('mssv', $value[$tableHeader['mssv'][0]])->first()) {
                    $dataTable[$key]['status'] = false;
                } else {
                    $student = new Student();
                    foreach ($tableHeader as $kFill => $vFill) {
                        if ($value[0] != null && $tableHeader[$kFill][0] != null) {
                            $student[$kFill] = $value[$tableHeader[$kFill][0]];
                        }
                    }
                    // check pass để random pass
                    if (isset($student->password)) {
                        $student->password = Hash::make($request->password);
                    } else {
                        $password_random = $this->randomPwd();
                        $student->password = Hash::make($password_random);
                    }

                    $student->create_by = $request->user()->id;
                    if ($student->save()) {
                    // if (true) {
                        if (isset($password_random)) {
                            $dataTable[$key]['password_random'] = $password_random;
                            $password_random_flag = true;
                        }
                        $dataTable[$key]['status'] = true;
                    } else {
                        $dataTable[$key]['status'] = false;
                    }
                }
            }
        }
        $data = [
            'dataTable' => $dataTable,
            'password_random' => $password_random_flag
        ];
        return response()->json($data, 201);
    }

    public function lockAccount(Request $request)
    {
        $user = Student::find($request->id);
        if ($user) {
            $user->isActive = 0;
            if ($user->save()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Khóa tài khoản ' . $user->mssv . ' thành công'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Có lỗi xảy ra'
                ]);
            }
        }
        return response()->json([], 404);
    }

    public function unlockAccount(Request $request)
    {
        $user = Student::find($request->id);
        if ($user) {
            $user->isActive = 1;
            if ($user->save()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Mở khóa tài khoản ' . $user->mssv . ' thành công'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Có lỗi xảy ra'
                ]);
            }
        }
        return response()->json([], 404);
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $user = Student::find($id);
        if ($user) {
            if (isset($request['id'])) {
                unset($request['id']);
            }
            if (isset($request['create_by'])) {
                unset($request['create_by']);
            }

            if (isset($request->email)) {
                if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email sai định dạng'
                    ]);
                }
            }
            $request->validate([
                'mssv' => 'required',
                'first_name' => 'required|min:2|max:50',
                'last_name' => 'required|min:2|max:20',
                'sex' => 'required|boolean',
                'birthday' => 'required|date',
                'address' => 'required|max:255'
            ]);

            if (isset($request->phone_number)) {
                $request->phone_number = preg_replace('/\s+/', '', $request->phone_number);
                if (!preg_match("/[0-9]{10}/", $request->phone_number)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Số điện thoại phải là 10 số (chấp nhận khoảng trắng)'
                    ]);
                }
            }
            $request->birthday = new DateTime($request->birthday);
            $request->birthday = $request->birthday->format('Y-m-d');
            if (!preg_match("/(19|20)[0-9]{2}\-(0?[1-9]|1[012])\-(1[0-9]|2[0-9]|3[01]|0?[1-9])/", $request->birthday)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ngày sinh không hợp lệ'
                ]);
            }

            $user->fill($request->all());
            $user->birthday = $request->birthday;
            if ($user->save()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Cập nhật thành công',
                    'data' => $user
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Cập nhật thất bại'
                ]);
            }
        } else {
            return response()->json([], 404);
        }
    }

    public function destroy($id)
    {
        //
    }

    public function login(Request $request)
    {
        $request->validate([
            'mssv' => 'required',
            'password' => 'required'
        ]);

        $user = Student::where('mssv', $request->mssv)->first();
        if ($user) {
            if (!$user->isActive) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tài khoản đã bị khóa'
                ]);
            } else {
                if (Hash::check($request->password, $user->password)) {

                    $tokenResult = $user->createToken('Students');
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
            'message' => 'Sai mssv hoặc mật khẩu'
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
        return response()->json(auth()->guard('api-student')->user());
    }
}
