<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassGroup;
use App\Models\ClassMember;
use App\Models\ClassSubject;
use App\Models\GroupMember;

class GroupController extends Controller
{
    private $helper;
    public function __construct() {
        $this->helper = new Helpers();
    }

    public function index($id)
    {
        $groups = ClassGroup::where("class_id", $id)->get();
        foreach ($groups as $key => $value) {
            $count = GroupMember::where("group_id", $value->id)->count();
            $groups[$key]["members"] = $count;
        }
        return response()->json($groups);
    }

    public function store(Request $request, $id, $type)
    {

        // $user = $this->helper->getUserData($request);
        // $type:
        // 1: Tạo nhóm mới
        // 2: Xếp nhóm ngẫu nhiên
        // 3: Xếp theo thứ tự danh sách
        if ($type == 1) {

        } else if ($type == 2) {
            $classSubject = ClassSubject::find($id);
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
                // Tạo nhóm
                $groupNew = ClassGroup::create([
                    "class_id" => $id,
                    "name" => "Nhóm " . $id . "#" . $numberGroup,
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
            $classSubject = ClassSubject::find($id);
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
                // Tạo nhóm
                $groupNew = ClassGroup::create([
                    "class_id" => $id,
                    "name" => "Nhóm " . $id . "#" . ($i+1),
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
}
