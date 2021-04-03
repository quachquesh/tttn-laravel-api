<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSubject;
use App\Models\Subject;
use App\Models\ClassMember;
use App\Models\Lecturer;

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

    public function allInfo($id)
    {
        $classSubject = ClassSubject::find($id);

        $subject = Subject::find($classSubject->subject_id);

        $classMembers = ClassMember::where("class_id", $id)
                            ->join("students", "class_members.student_id", "=", "students.id")
                            ->select("students.*")
                            ->get();

        $classLecturer = Lecturer::select("lecturers.*")
                            ->join("class_subjects", "class_subjects.create_by", "=", "lecturers.id")
                            ->where("class_subjects.id", $id)
                            ->first();

        return response()->json([
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
}
