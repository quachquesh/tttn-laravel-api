<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassMember;
use App\Models\Student;
use App\Models\ClassSubject;

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
        $fail = 0; // đã học lớp khác cùng môn học
        $success = 1; // thêm thành công
        $duplicate = 2; // đã có trong lớp
        $listData = [];
        $subjectCurrent = ClassSubject::find($request->classId)->subject_id;

        if (!isset($request->override) || empty($request->override) || $request->override == false) {
            foreach ($request->listStudent as $student) {
                // Nếu danh sách sv là mssv
                if (isset($request->type) && !empty($request->type) && $request->type == "mssv") {
                    $studentGet = Student::where("mssv", $student)->first();
                    $student = empty($studentGet['id']) ? null : $studentGet['id'];
                }
                if ($student) {
                    // Check student có trong lớp nào của môn học này chưa
                    $check = ClassSubject::where("class_subjects.subject_id", $subjectCurrent)
                                    ->join("class_members", "class_subjects.id", "=", "class_members.class_id")
                                    ->where("class_members.student_id", "=", $student)
                                    ->select("class_members.id")
                                    ->first();
                    // Check đã có trong lớp chưa
                    $studentFind = Student::find($student);
                    if (!ClassMember::where("student_id", $student)->where("class_id", $request->classId)->first()) {
                        if (empty($check)) {
                            $classMember = new ClassMember;
                            $classMember->student_id = $student;
                            $classMember->class_id = $request->classId;
                            $classMember->role = 0;
                            $test = $classMember->save();

                            if($classMember->save()) {
                                $studentFind->status = $success;
                                $studentFind->member_id = $classMember->id;
                                array_push($listData, $studentFind);
                            }
                        } else { // Nếu đã học lớp cùng môn
                            $studentFind->status = $fail;
                            array_push($listData, $studentFind);
                        }
                    } else { // Nếu đã có trong lớp
                        $studentFind->status = $duplicate;
                        array_push($listData, $studentFind);
                    }
                }
            }
            return response()->json($listData);
        } else { // override
            foreach ($request->listStudent as $student) {
                // Nếu danh sách sv là mssv
                if (isset($request->type) && !empty($request->type) && $request->type === "mssv") {
                    $studentGet = Student::where("mssv", $student)->first();
                    $student = empty($studentGet['id']) ? null : $studentGet['id'];
                }
                if ($student) {
                    $studentFind = Student::find($student);
                    // Check đã có trong lớp chưa
                    if (!ClassMember::where("student_id", $student)->where("class_id", $request->classId)->first()) {
                        // Lấy member id để xóa
                        $memberId = ClassSubject::where("class_subjects.subject_id", $subjectCurrent)
                                                ->join("class_members", "class_subjects.id", "=", "class_members.class_id")
                                                ->where("class_members.student_id", "=", $student)
                                                ->select("class_members.id")
                                                ->first();
                        if (ClassMember::destroy($memberId->id)) { // Xóa thành công
                            $classMember = new ClassMember;
                            $classMember->student_id = $student;
                            $classMember->class_id = $request->classId;
                            $classMember->role = 0;
                            if($classMember->save()) {
                                $studentFind->status = $success;
                                $studentFind->member_id = $classMember->id;
                                array_push($listData, $studentFind);
                            }
                        }
                    } else { // Nếu đã có trong lớp
                        $studentFind->status = $duplicate;
                        array_push($listData, $studentFind);
                    }
                }
            }
            return response()->json($listData);
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
