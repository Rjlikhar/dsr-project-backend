<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\GroupInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupInterface $groupService)
    {
        $this->groupService = $groupService;
    }


    public function index()
    {
        $groups = $this->groupService->getAllGroups();

        Log::info('Fetched all groups');
        return response()->json([
            'message' => 'Groups fetched successfully',
            'data' => $groups
        ]);
    }


    public function update(Request $request)
    {
    
        $request->validate([
            'group_id' => 'required|exists:dsr_groups,id',
        ]);

        $user = Auth::user();

        $updatedUser = $this->groupService->updateUserGroup(
            $user->id,
            $request->group_id
        );

        return response()->json([
            'message' => 'Group updated successfully',
            'data' => $updatedUser
        ], 200);
    }
}
