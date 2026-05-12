<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = Member::with('activeBorrowings');
        if (request()->has('search')) {

            $search = request()->search;

            $query->where(function (Builder $q) use ($search): void {

                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");


            });
        }
        if($request->has('status')){

            $query->where('status', $request->status);
        }
        $members = $query->paginate(10);
        return MemberResource::collection($members);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMemberRequest $request)
    {
        //
        $member= Member::create($request->validated());
        return new MemberResource($member);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)//Member $member
    {
        // $member = Member::with('borrowings')->findOrFail($member->id);
        // return new MemberResource($member);

           try {
            $member = Member::findOrFail($id);
            $member->load(['activeBorrowings','borrowings']);
            return new MemberResource($member);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        // we use patch method to update only the fields that are sent in the request, so we can use the same validation rules as store method instead use put method which requires all fields to be sent in the request

        $member->update($request->validated());
       // $member->load('borrowings');
        return new MemberResource($member);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $member = Member::findOrFail($id);
            if($member->activeBorrowings()->count() > 0) {
                return response()->json([
                    'message' => 'Cannot delete member with active borrowings',
                    'status' => false
                ], 422);
            }
            $member->delete();
            return response()->json([
                'message' => 'Member deleted successfully',
                'status' => true
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Member not found',
                'status' => false
            ], 404);
        }



    }
}
