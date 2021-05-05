<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Helpers;
use App\Models\Notify;
use App\Models\NotifyReply;
use App\Models\NotifyToMember;
use App\Models\ClassMember;
use App\Models\NotifyAttach;
use Illuminate\Support\Facades\Storage;


class NotifyController extends Controller
{
    private function uploadFile($notifyId, $listFiles, $user) {
        $files = [];
        $role = $user->role ? "lecturers" : "students";
        if (!empty($listFiles)) {
            foreach ($listFiles as $file) {
                // Check file tồn tại chưa
                if (Storage::disk("local")->exists($role."/".$user->id."/".$file->getClientOriginalName())) {
                    $number = 1;
                    $filename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                    $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                    while (Storage::disk("local")->exists($role."/".$user->id."/".$filename."_".$number.".".$extension)) {
                        $number++;
                    }
                    $path = $file->storeAs($role."/".$user->id, $filename."_".$number.".".$extension);
                } else {
                    $path = $file->storeAs($role."/".$user->id, $file->getClientOriginalName());
                }
                $file = NotifyAttach::create([
                    "notify_id" => $notifyId,
                    "file_name" => $file->getClientOriginalName(),
                    "link" => $path
                ]);
                array_push($files, $file);
            }
        }
        return $files;
    }

    public function store(Request $request, $classId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        if ($user != null) {
            // Giảng viên
            if ($user->role) {
                // Tạo thông báo
                $notify = Notify::create([
                    "lecturer_id" => $user->id,
                    "class_id" => $classId,
                    "content" => $request->content
                ]);
                // Xử lý file
                $notify->files = $this->uploadFile($notify->id, $request->listFiles, $user);
                // Tạo thành viên nhận thông báo
                foreach ($request->listMember as $id) {
                    NotifyToMember::create([
                        "member_id" => $id,
                        "notify_id" => $notify->id
                    ]);
                }
                unset($notify->lecturer_id);
                $notify->comment = [];
                return response()->json([
                    "status" => true,
                    "message" => "Đăng thông báo thành công",
                    "data" => $notify
                ]);
            } else { // Sinh viên
                $member = ClassMember::where("student_id", $user->id)->first();

                $notify = Notify::create([
                    "member_id" => $member->id,
                    "class_id" => $classId,
                    "content" => $request->content
                ]);
                // Xử lý file
                $notify->files = $this->uploadFile($notify->id, $request->listFiles, $user);
                // Get danh sách thành viên lớp đó
                $classMembers = ClassMember::where("class_id", $classId)->get();
                foreach ($classMembers as $member) {
                    NotifyToMember::create([
                        "member_id" => $member["id"],
                        "notify_id" => $notify->id
                    ]);
                }
                unset($notify->student_id);
                $notify->comment = [];
                return response()->json([
                    "status" => true,
                    "message" => "Đăng thông báo thành công",
                    "data" => $notify
                ]);
            }
        } else {
            return response()->json([], 500);
        }
    }

    public function update(Request $request, $notifyId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        if ($user != null) {
            $notify = Notify::find($notifyId);
            if ($notify) {
                // Giảng viên post
                if ($user->role && $notify->lecturer_id == $user->id) {
                    $notify->content = $request->content;
                    if ($notify->save()) {
                        $notifyListFiles = [];
                        $listFiles = NotifyAttach::where("notify_id", $notify->id)->get();
                        $listFiles_old = $request->listFiles_old;

                        if (!empty($listFiles_old) && !empty($listFiles)) {
                            foreach ($listFiles_old as $file_old) {
                                foreach ($listFiles as $index => $file) {
                                    if ($file_old == $file->id) {
                                        array_push($notifyListFiles, $listFiles[$index]);
                                        unset($listFiles[$index]);
                                    }
                                }
                            }
                        }
                        // Xóa file cũ
                        if (!empty($listFiles)) {
                            foreach ($listFiles as $index => $file) {
                                Storage::delete($file->link);
                                NotifyAttach::destroy($file->id);
                            }
                        }
                        // Thêm file mới
                        $listFiles_new = $this->uploadFile($notify->id, $request->listFiles_new, $user);
                        $notify->files = array_merge($notifyListFiles, $listFiles_new);

                        // Cập nhật listMembers
                        $listMembers = NotifyToMember::where("notify_id", $notifyId)->get();
                        $listMembers_update = $request->listMembers;
                        $listMembers_new = array();
                        // $listMembers: danh sách xóa
                        // $listMembers_new: ds thêm mới
                        foreach ($listMembers_update as $member) {
                            $check = false;
                            foreach ($listMembers as $key2 => $member2) {
                                if ($member == $member2->member_id) {
                                    $check = true;
                                    unset($listMembers[$key2]);
                                }
                            }
                            // Nếu không có trong danh sách thì cho vào ds thêm mới
                            if ($check == false) {
                                array_push($listMembers_new, $member);
                            }
                        }
                        // Xóa
                        foreach ($listMembers as $value) {
                            NotifyToMember::destroy($value->id);
                        }
                        // Thêm mới
                        foreach ($listMembers_new as $value) {
                            NotifyToMember::create([
                                "member_id" => $value,
                                "notify_id" => $notify->id
                            ]);
                        }

                        unset($notify->member_id);
                        unset($notify->lecturer_id);
                        return response()->json([
                            "status" => true,
                            "message" => "Sửa thông báo thành công",
                            "data" => $notify
                        ]);
                    } else {
                        return response()->json([
                            "status" => true,
                            "message" => "Sửa thông báo thất bại",
                            "data" => $notify
                        ]);
                    }
                } else if (!$user->role && $notify->member_id) {
                    $member = ClassMember::where("student_id", $user->id)->first();
                    if ($notify->member_id == $member->id) {
                        $notify->content = $request->content;
                        if ($notify->save()) {
                            $notifyListFiles = [];
                            $listFiles = NotifyAttach::where("notify_id", $notify->id)->get();
                            $listFiles_old = $request->listFiles_old;

                            if (!empty($listFiles_old) && !empty($listFiles)) {
                                foreach ($listFiles_old as $file_old) {
                                    foreach ($listFiles as $index => $file) {
                                        if ($file_old == $file->id) {
                                            array_push($notifyListFiles, $listFiles[$index]);
                                            unset($listFiles[$index]);
                                        }
                                    }
                                }
                            }
                            // Xóa file cũ
                            if (!empty($listFiles)) {
                                foreach ($listFiles as $index => $file) {
                                    Storage::delete($file->link);
                                    NotifyAttach::destroy($file->id);
                                }
                            }
                            // Thêm file mới
                            $listFiles_new = $this->uploadFile($notify->id, $request->listFiles_new, $user);
                            $notify->files = array_merge($notifyListFiles, $listFiles_new);

                            // Cập nhật listMembers
                            $listMembers = NotifyToMember::where("notify_id", $notifyId)->get();
                            $listMembers_class = ClassMember::where("class_id", $notify->class_id)->get();
                            $listMembers_new = array();
                            // $listMembers_new: ds thêm mới
                            foreach ($listMembers_class as $member) {
                                $check = false;
                                foreach ($listMembers as $key2 => $member2) {
                                    if ($member->id == $member2->member_id) {
                                        $check = true;
                                        unset($listMembers[$key2]);
                                    }
                                }
                                // Nếu không có trong danh sách thì cho vào ds thêm mới
                                if ($check == false) {
                                    array_push($listMembers_new, $member->id);
                                }
                            }
                            // Thêm mới
                            foreach ($listMembers_new as $value) {
                                NotifyToMember::create([
                                    "member_id" => $value,
                                    "notify_id" => $notify->id
                                ]);
                            }

                            unset($notify->member_id);
                            unset($notify->lecturer_id);
                            return response()->json([
                                "status" => true,
                                "message" => "Sửa thông báo thành công",
                                "data" => $notify
                            ]);
                        } else {
                            return response()->json([
                                "status" => true,
                                "message" => "Sửa thông báo thất bại",
                                "data" => $notify
                            ]);
                        }
                    }
                }
            }
        }
        return response()->json([], 500);
    }

    public function destroy(Request $request, $classId, $notifyId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);


        $notify = Notify::find($notifyId);
        $listFile = NotifyAttach::where("notify_id", $notify->id)->get();
        if ($notify) {
            // member đăng
            if ($notify->member_id && !$user->role) {
                $member = ClassMember::where("student_id", $user->id)->where("class_id", $classId)->first();
                if ($member->id == $notify->member_id) {
                    if (Notify::destroy($notifyId)) {
                        foreach ($listFile as $value) {
                            Storage::delete($value->link);
                        }
                        return response()->json([
                            "status" => true,
                            "message" => "Xóa thông báo thành công"
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Xóa thông báo thất bại"
                        ]);
                    }
                }
            } else if ($user->id == $notify->lecturer_id) {
                if (Notify::destroy($notifyId)) {
                    foreach ($listFile as $value) {
                        Storage::delete($value->link);
                    }
                    return response()->json([
                        "status" => true,
                        "message" => "Xóa thông báo thành công"
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Xóa thông báo thất bại"
                    ]);
                }
            }
        }
        return response()->json([], 404);
    }

    public function getNotifyToMember($notifyId)
    {
        // $data = NotifyToMember::where("notify_id", $notifyId)
        //                         ->join("class_members", "notify_to_members.member_id", "=", "class_members.id")
        //                         ->join("students", "students.id", "=", "class_members.student_id")
        //                         ->get();
        $data = NotifyToMember::where("notify_id", $notifyId)->get();
        return response()->json($data);
    }

    public function replyStore(Request $request, $notifyId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        // Check gv
        if ($user->role) {
            $reply = NotifyReply::create([
                "notify_id" => $notifyId,
                "reply_by_lecturer" => $user->id,
                "content" => $request->content
            ]);
            unset($reply->reply_by_lecturer);
            return response()->json([
                "status" => true,
                "message" => "Nhận xét thành công",
                "data" => $reply
            ]);
        } else {
            // check sv có nhận được thông báo không
            $member = ClassMember::where("student_id", $user->id)->first();
            if (NotifyToMember::where("member_id", $member->id)->where("notify_id", $notifyId)->first()) {
                $reply = NotifyReply::create([
                    "notify_id" => $notifyId,
                    "reply_by_member" => $member->id,
                    "content" => $request->content
                ]);
                unset($reply->reply_by_member);
                return response()->json([
                    "status" => true,
                    "message" => "Nhận xét thành công",
                    "data" => $reply
                ]);
            } else {
                return response()->json([
                    "status" => false,
                    "message" => "Sinh viên không có quyền nhận xét"
                ]);
            }
        }
    }

    public function replyUpdate(Request $request, $classId, $replyId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        $reply = NotifyReply::Find($replyId);
        if ($reply) {
            $reply->content = $request->content;
            // member đăng
            if ($reply->reply_by_member && !$user->role) {
                $member = ClassMember::where("student_id", $user->id)->where("class_id", $classId)->first();
                if ($member->id == $reply->reply_by_member) {
                    if ($reply->save()) {
                        return response()->json([
                            "status" => true,
                            "message" => "Sửa nhận xét thành công",
                            "data" => $reply
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Sửa nhận xét thất bại"
                        ]);
                    }
                }
            } else if ($user->id == $reply->reply_by_lecturer) {
                if ($reply->save()) {
                    return response()->json([
                        "status" => true,
                        "message" => "Sửa nhận xét thành công",
                        "data" => $reply
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Sửa nhận xét thất bại"
                    ]);
                }
            }
        }
        return response()->json([], 404);
    }

    public function replyDestroy(Request $request, $classId, $replyId)
    {
        $helper = new Helpers;
        $user = $helper->getUserData($request);

        $reply = NotifyReply::Find($replyId);
        if ($reply) {
            // member đăng
            if ($reply->reply_by_member && !$user->role) {
                $member = ClassMember::where("student_id", $user->id)->where("class_id", $classId)->first();
                if ($member->id == $reply->reply_by_member) {
                    if (NotifyReply::destroy($replyId)) {
                        return response()->json([
                            "status" => true,
                            "message" => "Xóa nhận xét thành công"
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Xóa nhận xét thất bại"
                        ]);
                    }
                }
            } else if ($user->id == $reply->reply_by_lecturer) {
                if (NotifyReply::destroy($replyId)) {
                    return response()->json([
                        "status" => true,
                        "message" => "Xóa nhận xét thành công"
                    ]);
                } else {
                    return response()->json([
                        "status" => false,
                        "message" => "Xóa nhận xét thất bại"
                    ]);
                }
            }
        }
        return response()->json([], 404);
    }
}
