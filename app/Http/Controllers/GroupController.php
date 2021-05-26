<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassGroup;
use App\Models\ClassMember;
use App\Models\ClassSubject;
use App\Models\GroupMember;
use App\Models\Student;
use App\Models\TicketGroup;

class GroupController extends Controller
{
    private $helper;
    public function __construct() {
        $this->helper = new Helpers();
    }

    public function index(Request $request, $id)
    {
        $user = $this->helper->getUserData($request);
        if ($user) {
            if ($user->role) {
                $groups = ClassGroup::where("class_id", $id)->get();
                foreach ($groups as $key => $value) {
                    $count = GroupMember::where("group_id", $value->id)->count();
                    $groups[$key]["members"] = $count;
                }
                return response()->json($groups);
            }
            // Check có trong lớp không
            $member = ClassMember::where("class_id", $id)->where("student_id", $user->id)->first();
            if ($member) {
                $groups = ClassGroup::where("class_id", $id)->get();
                foreach ($groups as $key => $value) {
                    $count = GroupMember::where("group_id", $value->id)->count();
                    $ticket = TicketGroup::where("member_id", $member->id)
                                        ->where("group_going", $value->id)
                                        ->where("status", "0")
                                        ->first();
                    $groups[$key]["members"] = $count;
                    $groups[$key]["ticket"] = $ticket;
                }
                return response()->json($groups);
            } else {
                return response("", 404);
            }
        } else {
            return response("", 404);
        }
    }

    public function getMyGroup(Request $request, $id)
    {
        $user = $this->helper->getUserData($request);
        if (!$user->role) {
            // Lấy thông tin nhóm đã vào
            $member = ClassMember::where("student_id", $user->id)->where("class_id", $id)->first();
            $groups = ClassGroup::where("class_id", $id)->get();
            foreach ($groups as $key => $value) {
                $check = GroupMember::where("member_id", $member->id)
                                        ->where("group_id", $value->id)
                                        ->first();
                if ($check) {
                    // Lấy tất cả thành viên trong nhóm
                    $groupMembers = GroupMember::where("group_members.group_id", $value->id)
                                                ->join("class_members", "class_members.id", "=", "group_members.member_id")
                                                ->join("students", "students.id", "=", "class_members.student_id")
                                                ->select("students.*", "group_members.role")
                                                ->get();
                    $groups[$key]["members"] = $groupMembers;
                    return response()->json($groups[$key]);
                }
            }
        }
        return response()->json([]);
    }

    public function store(Request $request, $id, $type)
    {

        $user = $this->helper->getUserData($request);
        if (!$user) {
            return response("", 404);
        }
        // $type:
        // 1: Tạo nhóm mới
        // -- gv
        // 2: Xếp nhóm ngẫu nhiên
        // 3: Xếp theo thứ tự danh sách
        if ($type == 1) {
            $member = ClassMember::where("student_id", $user->id)->where("class_id", $id)->first();
            if ($member) {
                // Check xem đã có nhóm chưa
                $allGroup = ClassGroup::where("class_id", $id)->get();
                foreach ($allGroup as $key => $value) {
                    $check = GroupMember::where("member_id", $member->id)->where("group_id", $value->id)->first();
                    if (!!$check) {
                        return response()->json([
                            "status" => false,
                            "message" => "Bạn đã tham gia nhóm"
                        ]);
                    }
                }
                $name = "";
                $description = "";
                // Nếu tên lớp để trống thì lấy lên nhóm trưởng
                if (!isset($request->name) || empty($request->name)) {
                    $name = $user->first_name . " " . $user->last_name;
                } else {
                    $name = $request->name;
                }

                if (isset($request->description)) {
                    $description = $request->description;
                }

                $groupNew = ClassGroup::create([
                    "class_id" => $id,
                    "name" => $name,
                    "description" => $description,
                    "note" => ""
                ]);
                $memberNew = GroupMember::create([
                    "member_id" => $member->id,
                    "group_id" => $groupNew->id,
                    "role" => 1
                ]);
                $groupNew->members = 1;
                return response()->json([
                    "status" => true,
                    "message" => "Tạo nhóm thành công",
                    "data" => $groupNew
                ]);
            } else {
                return response("", 404);
            }
        } else if ($type == 2) {
            if (!$user->role) {
                return response("", 500);
            }
            $classSubject = ClassSubject::find($id);
            // Không phải gv của lớp
            if (!$classSubject || $user->id != $classSubject->create_by) {
                return response("", 500);
            }
            $listMember = ClassMember::where("class_id", $id)->get();
            // Lọc sinh viên chưa vào nhóm
            $listGroups = ClassGroup::where("class_id", $id)->get();
            $listMember2 = []; // Danh sách sinh viên đã có nhóm
            foreach ($listGroups as $key => $value) {
                $members = GroupMember::where("group_id", $value->id)->get();
                foreach ($members as $key => $value) {
                    array_push($listMember2, $value);
                }
            }
            foreach ($listMember2 as $value2) {
                foreach ($listMember as $key => $value) {
                    if ($value->id == $value2->member_id) {
                        unset($listMember[$key]);
                        break;
                    }
                }
            }
            // Chuyển object sang array và đặt lại index
            $listMember = $this->helper->object2Array($listMember);
            // Kết thúc lọc ---
            $lengthMember = count($listMember);
            $listGroups = [];
            $numberGroup = 1;

            while($lengthMember > 0) {
                $listRand = [];
                $count = 0;
                // Nếu số lượng sv còn lại > thành viên tối đa thì tạo 1 list sv random
                if ($lengthMember >= $classSubject->maximum_group_member) {
                    while($count < $classSubject->maximum_group_member) {
                        $index = rand(0, $lengthMember - 1);
                        array_push($listRand, $listMember[$index]);
                        unset($listMember[$index]);
                        // Chuyển object sang array và đặt lại index
                        $listMember = $this->helper->object2Array($listMember);
                        $lengthMember--;
                        $count++;
                    }
                } else {
                    foreach ($listMember as $key => $value) {
                        array_push($listRand, $listMember[$key]);
                    }
                    $count = $lengthMember;
                    $lengthMember = 0;
                }
                // Lấy tên nhóm trưởng làm tên nhóm
                $student = Student::find($listRand[0]->student_id);
                // Tạo nhóm
                $groupNew = ClassGroup::create([
                    "class_id" => $id,
                    // "name" => "Nhóm " . $id . "#" . $numberGroup,
                    "name" => $student->first_name . " " . $student->last_name,
                    "description" => "",
                    "note" => ""
                ]);
                $numberGroup++;

                foreach ($listRand as $key => $value) {
                    // Người đầu tiên làm nhóm trưởng
                    $memberNew = GroupMember::create([
                        "member_id" => $value->id,
                        "group_id" => $groupNew->id,
                        "role" => $key == 0 ? 1 : 0
                    ]);
                }
                $groupNew->members = $count;
                array_push($listGroups, $groupNew);
            }
            return response()->json([
                "status" => true,
                "message" => "Xếp nhóm ngẫu nhiên thành công",
                "data" => $listGroups
            ]);
        } else if ($type == 3) {
            if (!$user->role) {
                return response("", 500);
            }
            $classSubject = ClassSubject::find($id);
            // Không phải gv của lớp
            if (!$classSubject || $user->id != $classSubject->create_by) {
                return response("", 500);
            }
            $listMember = ClassMember::where("class_id", $id)->get();
            // Lọc sinh viên chưa vào nhóm
            $listGroups = ClassGroup::where("class_id", $id)->get();
            $listMember2 = []; // Danh sách sinh viên đã có nhóm
            foreach ($listGroups as $key => $value) {
                $members = GroupMember::where("group_id", $value->id)->get();
                foreach ($members as $key => $value) {
                    array_push($listMember2, $value);
                }
            }
            foreach ($listMember2 as $value2) {
                foreach ($listMember as $key => $value) {
                    if ($value->id == $value2->member_id) {
                        unset($listMember[$key]);
                        break;
                    }
                }
            }
            // Chuyển object sang array và đặt lại index
            $listMember = $this->helper->object2Array($listMember);
            // Kết thúc lọc ---

            $lengthMember = count($listMember);
            $maxGroup = ceil($lengthMember/$classSubject->maximum_group_member);
            $listGroups = [];

            for ($i=0; $i < $maxGroup; $i++) {
                // Lấy tên nhóm trưởng làm tên nhóm
                $student = Student::find($listMember[$i*$classSubject->maximum_group_member]->student_id);
                // Tạo nhóm
                $groupNew = ClassGroup::create([
                    "class_id" => $id,
                    // "name" => "Nhóm " . $id . "#" . ($i+1),
                    "name" => $student->first_name . " " . $student->last_name,
                    "description" => "",
                    "note" => ""
                ]);
                $key = 0;
                for ($j=$i*$classSubject->maximum_group_member; $j < $i*$classSubject->maximum_group_member+$classSubject->maximum_group_member; $j++) {
                    if ($j < $lengthMember) {
                        // Người đầu tiên làm nhóm trưởng
                        $memberNew = GroupMember::create([
                            "member_id" => $listMember[$j]->id,
                            "group_id" => $groupNew->id,
                            "role" => $key == 0 ? 1 : 0
                        ]);
                        $key++;
                    } else {
                        break;
                    }
                }
                $groupNew->members = $key;
                array_push($listGroups, $groupNew);
            }
            return response()->json([
                "status" => true,
                "message" => "Xếp nhóm theo thứ tự thành công",
                "data" => $listGroups
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "Sai Type tạo nhóm"
        ]);
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


    // ---- Ticket Group ----
    public function createTicket(Request $request, $id, $type)
    {
        $user = $this->helper->getUserData($request);
        if ($user) {
            // Check có trong lớp không
            $member = ClassMember::where("class_id", $id)->where("student_id", $user->id)->first();
            if ($member) {
                // Check xem đã gửi yêu cầu nào chưa
                if ($type != 2) {
                    $ticket = TicketGroup::where("member_id", $member->id)
                                        ->where("ticket_type", $type)
                                        ->where("status", "0")
                                        ->first();
                    if ($ticket) {
                        return response()->json([
                            "status" => false,
                            "message" => "Hãy đợi yêu cầu trước được duyệt hoặc hủy yêu cầu trước"
                        ]);
                    }

                    // check vào group nào chưa
                    $groups = ClassGroup::where("class_id", $id)->get();
                    $myGroup = [];
                    foreach ($groups as $value) {
                        $memberGroup = GroupMember::where("member_id", $member->id)
                                                    ->where("group_id", $value->id)
                                                    ->first();
                        if ($memberGroup) {
                            $myGroup = $value;
                            break;
                        }
                    }

                    // check nhóm full thành viên chưa
                    if (isset($request->group_going)) {
                        $count = GroupMember::where("group_id", $request->group_going)->count();
                        $classSubject = ClassSubject::find($id);
                        if ($classSubject->maximum_group_member <= $count) {
                            return response()->json([
                                "status" => false,
                                "message" => "Nhóm đã đủ người"
                            ]);
                        }
                    }
                }

                if ($type == 0) {
                    // Nếu đã có trong nhóm khác
                    if ($memberGroup) {
                        return response()->json([
                            "status" => false,
                            "message" => "Bạn đã có nhóm"
                        ]);
                    } else {
                        $ticketNew = TicketGroup::create([
                            "member_id" => $member->id,
                            "ticket_type" => $type,
                            "reason" => "Xin gia nhập nhóm",
                            "group_going" => $request->group_going,
                            "status" => 0
                        ]);
                        return response()->json([
                            "status" => true,
                            "message" => "Gửi yêu cầu thành công. Hãy đợi nhóm trưởng duyệt!",
                            "data" => $ticketNew
                        ]);
                    }
                } else if ($type == 1) {
                    // Nếu chưa vào nhóm nào
                    if (!$memberGroup) {
                        return response()->json([
                            "status" => false,
                            "message" => "Bạn chưa có nhóm"
                        ]);
                    } else if (!empty($myGroup) && $myGroup->id != $request->group_going) {
                        $ticketNew = TicketGroup::create([
                            "member_id" => $member->id,
                            "ticket_type" => $type,
                            "reason" => $request->reason,
                            "group_now" => $myGroup->id,
                            "group_going" => $request->group_going,
                            "status" => 0
                        ]);
                        return response()->json([
                            "status" => true,
                            "message" => "Gửi yêu cầu thành công. Hãy đợi giảng viên duyệt",
                            "data" => $ticketNew
                        ]);
                    } else {
                        return response("", 500);
                    }
                } else if ($type == 2) {
                    // Check xem phải nhóm trưởng k
                    if (!empty($memberGroup) && $memberGroup->role == 1) {
                        $ticketNew = TicketGroup::create([
                            "member_id" => $member->id,
                            "member_target" => $request->member_target,
                            "ticket_type" => $type,
                            "reason" => $request->reason,
                            "group_now" => $myGroup->id,
                            "status" => 0
                        ]);
                        return response()->json([
                            "status" => true,
                            "message" => "Gửi yêu cầu thành công. Hãy đợi giảng viên duyệt",
                            "data" => $ticketNew
                        ]);
                    } else {
                        return response("", 500);
                    }
                }
            }
        }
    }

    public function getTickets(Request $request, $id)
    {
        $user = $this->helper->getUserData($request);
        if ($user) {
            if ($user->role) {
                $groups = ClassGroup::where("class_id", $id)->get();
                $tickets = [];
                foreach ($groups as $key => $value) {
                    $ticket = TicketGroup::where("group_now", $value->id)
                                        ->whereIn("ticket_type", [1, 2])
                                        ->where("status", 0)
                                        ->get();
                    foreach ($ticket as $value2) {
                        array_push($tickets, $value2);

                        // $check = false;
                        // foreach ($tickets as $value3) {
                        //     if ($value2->id == $value3->id) {
                        //         $check = true;
                        //     }
                        // }
                        // if ($check == false) {
                        //     array_push($tickets, $value2);
                        // }
                    }
                }
                foreach ($tickets as $key => $value) {
                    // Nếu kick người thì lấy thông tin người bị kick
                    if (!empty($value->member_target)) {
                        $student = ClassMember::where("class_members.id", $value->member_target)
                                            ->join("students", "students.id", "=", "class_members.student_id")
                                            ->select("students.*")
                                            ->first();
                        $tickets[$key]["member_target"] = $student;
                    }
                    // Người gửi yêu cầu
                    $student = ClassMember::where("class_members.id", $value->member_id)
                                            ->join("students", "students.id", "=", "class_members.student_id")
                                            ->select("students.*")
                                            ->first();
                    $tickets[$key]["author"] = $student;
                }
                return response()->json($tickets);
            }
            // Check có trong lớp không
            $member = ClassMember::where("class_id", $id)->where("student_id", $user->id)->first();
            if ($member) {
                $groups = ClassGroup::where("class_id", $id)->get();
                $myGroup = [];
                foreach ($groups as $value) {
                    $memberGroup = GroupMember::where("member_id", $member->id)
                                                ->where("group_id", $value->id)
                                                ->first();
                    if ($memberGroup) {
                        $myGroup = $value;
                        break;
                    }
                }
                // Check xem phải nhóm trưởng k
                if (!empty($memberGroup) && $memberGroup->role == 1) {
                    $tickets = TicketGroup::where("group_going", $myGroup->id)
                                            ->where("status", 0)
                                            ->where("ticket_type", 0)
                                            ->get();
                    foreach ($tickets as $key => $value) {
                        $student = ClassMember::where("class_members.id", $value->member_id)
                                                ->join("students", "students.id", "=", "class_members.student_id")
                                                ->select("students.*")
                                                ->first();
                        $tickets[$key]["author"] = $student;
                    }
                    return response()->json($tickets);
                }
            }
            return response("", 404);
        }
        return response("", 500);
    }

    public function updateTicket(Request $request, $id, $ticket_id)
    {
        $user = $this->helper->getUserData($request);
        if ($user) {
            if ($user->role) {
                $classSubject = ClassSubject::find($id);
                if (!empty($classSubject) && $classSubject->create_by == $user->id) {
                    $ticket = TicketGroup::find($ticket_id);
                    if (empty($ticket)) {
                        return response()->json([
                            "status" => false,
                            "message" => "Yêu cầu không tồn tại"
                        ]);
                    } else if (
                        $ticket->status == 0 &&     // trạng thái chờ
                        ($ticket->ticket_type == 1 || $ticket->ticket_type == 2) // Chuyển nhóm/kick
                    ) {
                        $ticket->status = $request->status;
                        if ($ticket->save()) {
                            if ($ticket->status == 1) {
                                // Xử lý chuyển nhóm
                                if ($ticket->ticket_type == 1) {
                                    // Xóa khỏi nhóm
                                    $groupMember = GroupMember::where("member_id", $ticket->member_id)->where("group_id", $ticket->group_now)->first();
                                    GroupMember::destroy($groupMember->id);
                                    // Tạo bên nhóm mới
                                    GroupMember::create([
                                        "member_id" => $ticket->member_id,
                                        "group_id" => $ticket->group_going,
                                        "role" => 0
                                    ]);
                                }
                                return response()->json([
                                    "status" => true,
                                    "message" => "Duyệt thành công"
                                ]);
                            } else if ($ticket->status == 2) {
                                return response()->json([
                                    "status" => true,
                                    "message" => "Hủy thành công"
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => false,
                                "message" => "Có lỗi xảy ra"
                            ]);
                        }
                    }
                } else {
                    return response("", 500);
                }
            }
            // Check có trong lớp không
            $member = ClassMember::where("class_id", $id)->where("student_id", $user->id)->first();
            if ($member) {
                $groups = ClassGroup::where("class_id", $id)->get();
                $myGroup = [];
                foreach ($groups as $value) {
                    $memberGroup = GroupMember::where("member_id", $member->id)
                                                ->where("group_id", $value->id)
                                                ->first();
                    if ($memberGroup) {
                        $myGroup = $value;
                        break;
                    }
                }
                // Check nhóm trưởng
                if (!empty($memberGroup) && $memberGroup->role == 1) {
                    $ticket = TicketGroup::find($ticket_id);
                    if (empty($ticket)) {
                        return response()->json([
                            "status" => false,
                            "message" => "Yêu cầu không tồn tại"
                        ]);
                    } else if (
                        $ticket->group_going == $myGroup->id && // group của tôi
                        $ticket->status == 0 &&     // trạng thái chờ
                        $ticket->ticket_type == 0   // xin vào nhóm
                    ) {
                        $ticket->status = $request->status;
                        if ($ticket->save()) {
                            if ($ticket->status == 1) {
                                GroupMember::create([
                                    "member_id" => $ticket->member_id,
                                    "group_id" => $ticket->group_going,
                                    "role" => 0
                                ]);
                                return response()->json([
                                    "status" => true,
                                    "message" => "Duyệt thành công"
                                ]);
                            } else if ($ticket->status == 2) {
                                return response()->json([
                                    "status" => true,
                                    "message" => "Hủy thành công"
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => false,
                                "message" => "Có lỗi xảy ra"
                            ]);
                        }
                    }
                }
            }
        }
        return response()->json("", 500);
    }

    public function destroyTicket(Request $request, $id, $ticket_id)
    {
        $user = $this->helper->getUserData($request);
        if ($user) {
            $member = ClassMember::where("student_id", $user->id)->where("class_id", $id)->first();
            if ($member) {
                $ticket = TicketGroup::find($ticket_id);
                if ($ticket->status != 0) {
                    return response()->json([
                        "status" => false,
                        "message" => "Yêu cầu đã được xử lý"
                    ]);
                } else if ($ticket->member_id == $member->id) {
                    if (TicketGroup::destroy($ticket_id)) {
                        return response()->json([
                            "status" => true,
                            "message" => "Hủy yêu cầu thành công"
                        ]);
                    } else {
                        return response()->json([
                            "status" => false,
                            "message" => "Hủy yêu cầu thất bại"
                        ]);
                    }
                }
            }
        }
        return response("", 500);
    }
}
