<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Models\ClassMember;
use App\Models\Lecturer;
use App\Http\Controllers\Helpers;
use App\Models\Notify;
use App\Models\NotifyReply;
use App\Models\NotifyToMember;
use App\Models\NotifyAttach;
use App\Models\Student;

class ClassSubjectController extends Controller
{
    private $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    private function randomKey()
    {
        return substr(str_shuffle($this->permitted_chars), 0, 6);
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $classSubjects = ClassMember::where("student_id", $userId)
                                    ->join("class_subjects", "class_subjects.id", "=", "class_members.class_id")
                                    ->join("subjects", "class_subjects.subject_id", "=", "subjects.id")
                                    ->select("class_subjects.*", "subjects.name as subject_name", "subjects.description as subject_description")
                                    ->get();
        return response()->json($classSubjects);
    }

    public function getAllClass(Request $request)
    {
        $userId = $request->user()->id;
        $classSubjects = ClassSubject::where("create_by", $userId)
                                    ->get();
        return response()->json($classSubjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'subject_id' => 'required'
        ]);

        $subject = Subject::find($request->subject_id);
        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => "Môn học không tồn tại"
            ]);
        } else {
            $class = new ClassSubject;
            $class->fill($request->all());
            $class->key = $this->randomKey();
            $class->create_by = $request->user()->id;
            if ($class->save()) {
                return response()->json([
                    'status' => true,
                    'message' => "Tạo lớp học thành công",
                    'data' => $class
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Tạo lớp học thất bại"
                ]);
            }
        }
    }

    public function show(Request $request, $id)
    {
        $classSubject = ClassSubject::find($id);
        return response()->json($classSubject);
    }

    public function showBySubject(Request $request, $subjectId)
    {
        $classSubjects = ClassSubject::where('subject_id', $subjectId)
                            ->join("subjects", "class_subjects.subject_id", "=", "subjects.id")
                            ->select("class_subjects.*", "subjects.name as subject_name", "subjects.description as subject_description")
                            ->where("create_by", $request->user()->id)
                            ->get();
        return response()->json($classSubjects);
    }

    public function allInfo(Request $request, $id)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);
        $user_member = ClassMember::where("student_id", $user->id)->where("class_id", $id)->first();

        $checkMember = !!$user->role; // check user có trong lớp học không

        $classMembers = ClassMember::where("class_id", $id)
                            ->join("students", "class_members.student_id", "=", "students.id")
                            ->select("students.*", "class_members.id as member_id")
                            ->get();
        // Lọc trường password + thành viên trong lớp
        foreach ($classMembers as $key => $value) {
            if (isset($value['password'])) {
                unset($classMembers[$key]['password']);
            }
            if (!$user->role && $value["id"] == $user->id) {
                $checkMember = true;
            }
        }
        // check member có trong lớp học không
        if (!$checkMember) return response()->json([], 404);

        // ===== THÔNG BÁO ====
        // Giảng viên thì nhận hết thông báo
        $notifies = Notify::where("class_id", $id)->orderBy('created_at','desc')->get();
        // Nếu là sinh viên thì check có trong danh sách nhận thông báo không
        if (!$user->role) {
            $accessNotify = NotifyToMember::where("member_id", $user_member->id)->get();
            foreach ($notifies as $key => $value) {
                $checkClass = false;
                foreach ($accessNotify as $key2 => $value2) {
                    if ($value->id == $value2->notify_id) {
                        $checkClass = true;
                        unset($accessNotify[$key2]);
                    }
                }
                // Nếu sinh viên không được nhận thông báo
                if (!$checkClass) {
                    unset($notifies[$key]);
                }
            }
        }
        // Lấy thông tin người đăng
        foreach ($notifies as $key => $value) {
            // Nếu không phải giảng viên đăng
            if ($value->lecturer_id == null) {
                unset($notifies[$key]->lecturer_id);
                $student_id = ClassMember::find($value->member_id)->student_id;
                $notifies[$key]->author = Student::find($student_id);
                unset($notifies[$key]->member_id);
            } else {
                unset($notifies[$key]->member_id);
                $notifies[$key]->author = Lecturer::find($value->lecturer_id);
                unset($notifies[$key]->lecturer_id);
            }

            // get comment
            $comment = NotifyReply::where("notify_id", $value->id)->get();
            foreach ($comment as $kCmt => $vCmt) {
                // Nếu không phải giảng viên reply
                if ($vCmt->reply_by_lecturer == null) {
                    unset($comment[$kCmt]->reply_by_lecturer);
                    $student_id = ClassMember::find($vCmt->reply_by_member)->student_id;
                    $comment[$kCmt]->author = Student::find($student_id);
                    unset($comment[$kCmt]->reply_by_member);
                } else {
                    unset($comment[$kCmt]->reply_by_member);
                    $comment[$kCmt]->author = Lecturer::find($vCmt->reply_by_lecturer);
                    unset($comment[$kCmt]->reply_by_lecturer);
                }
            }
            $notifies[$key]->comment = $comment;
        }
        // Lấy list file upload của thông báo
        foreach ($notifies as $key => $value) {
            $listFiles = NotifyAttach::where("notify_id", $value->id)->get();
            $notifies[$key]->files = $listFiles;
        }
        $notifies = $this->object2Array($notifies);
        // ===== KẾT THÚC THÔNG BÁO ====

        // ===== THÔNG TIN LỚP MÔN HỌC ====
        $classSubject = ClassSubject::find($id);

        // ===== THÔNG TIN MÔN HỌC ====
        $subject = Subject::find($classSubject->subject_id);

        // ===== THÔNG TIN GIẢNG VIÊN ====
        $classLecturer = Lecturer::select("lecturers.*")
                            ->join("class_subjects", "class_subjects.create_by", "=", "lecturers.id")
                            ->where("class_subjects.id", $id)
                            ->first();

        return response()->json([
            "notifies" => $notifies,
            "classSubject" => $classSubject,
            "subject" => $subject,
            "classMembers" => $classMembers,
            "classLecturer" => $classLecturer
        ]);
    }

    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function object2Array($obj)
    {
        $result = array();
        foreach ($obj as $value) {
            array_push($result, $value);
        }
        return $result;
    }
}
