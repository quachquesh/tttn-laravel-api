<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassMember;
use App\Models\Student;

class ClassMemberController extends Controller
{
    public function index($id)
    {
        $members = ClassMember::where("class_id", $id)
                            ->join("students", "class_members.student_id", "=", "students.id")
                            ->select("students.*", "class_members.id as member_id")
                            ->get();

        foreach ($members as $key => $value) {
            if (isset($value['password'])) {
                unset($members[$key]['password']);
            }
        }

        return response()->json($members);
    }
    public function store(Request $request)
    {
        // listStudent: chứa list id student
        // classId
        // override: chuyển qua lớp mới
        // type: null || mssv
        $fail = 0; // thêm thất bại
        $success = 1; // thêm thành công
        $duplicate = 2; // đã có trong lớp
        $listData = [];
        // $subjectCurrent = ClassSubject::find($request->classId)->subject_id;

        foreach ($request->listStudent as $mssv) {
            $studentFind = Student::where("mssv", $mssv)->first();
            // chuyển từ mssv -> id student
            $student = empty($studentFind['id']) ? null : $studentFind['id'];
            // Nếu mssv không tồn tại
            if ($student === null) {
                $data = array('mssv' => $mssv, 'status' => $fail);
                array_push($listData, $data);
            }
            // Check đã có trong lớp chưa
            else if (!ClassMember::where("student_id", $student)->where("class_id", $request->classId)->first()) {
                $classMember = new ClassMember;
                $classMember->student_id = $student;
                $classMember->class_id = $request->classId;
                $classMember->role = 0;

                if($classMember->save()) {
                    $studentFind->status = $success;
                    $studentFind->member_id = $classMember->id;
                    array_push($listData, $studentFind);
                } else { // Thêm thất bại
                    $studentFind->status = $fail;
                    array_push($listData, $studentFind);
                }
            } else { // Nếu đã có trong lớp
                $studentFind->status = $duplicate;
                array_push($listData, $studentFind);
            }
        }
        return response()->json($listData);
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
        if (ClassMember::destroy($id)) {
            return response()->json([
                "status" => true,
                "message" => "Xóa thành công"
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Xóa thất bại"
            ]);
        }
    }

    public function destroyList(Request $request)
    {
        $listMember = $request->listMember;
        $listSuccess = [];
        foreach ($listMember as $id) {
            if (ClassMember::destroy($id)) {
                array_push($listSuccess, $id);
            }
        }
        return response()->json([
            "status" => true,
            "message" => "Xóa thành viên thành công",
            "data" => $listSuccess
        ]);
    }
}
