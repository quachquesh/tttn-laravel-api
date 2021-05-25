<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\ClassSubject;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $subjects = Subject::all();
        return response()->json($subjects);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'img' => 'required',
            'semester' => 'required|numeric'
        ]);
        if (isset($request->description)) {
            $description = $request->description;
        } else {
            $description = "";
        }
        $checkSubject = Subject::where("name", $request->name)
                                ->where("description", $description)
                                ->first();
        if ($checkSubject) {
            return response()->json([
                'status' => false,
                'message' => "Môn học đã tồn tại"
            ]);
        } else {
            $subject = new Subject;
            $subject->fill($request->all());
            if ($subject->save()) {
                return response()->json([
                    'status' => true,
                    'message' => "Tạo môn học thành công",
                    'data' => $subject
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => "Tạo môn học thất bại"
                ]);
            }
        }
    }

    public function show($id)
    {
        $subject = Subject::find($id);
        return response()->json($subject);
    }

    public function showByUserId(Request $request)
    {
        $userId = $request->user()->id;
        $subjects = ClassSubject::where("create_by", $userId)
                                ->join("subjects", "class_subjects.subject_id", "=", "subjects.id")
                                ->select("subjects.*")
                                ->get();
        $subjects = $subjects->groupBy("id");
        $result = [];
        foreach ($subjects as $value) {
            array_push($result, $value[0]);
        }
        return response()->json($result);
    }

    public function showAll(Request $request)
    {
        $userId = $request->user()->id;
        $subjects = Subject::get();
        foreach ($subjects as $key => $value) {
            $count = ClassSubject::where("subject_id", $value->id)
                                    ->where("create_by", $userId)
                                    ->count();
            $subjects[$key]["number_of_class"] = $count;
        }
        return response()->json($subjects);
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
