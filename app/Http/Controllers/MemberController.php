<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Resources\BorrowingResource;
use App\Http\Resources\MemberResource;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MemberController extends Controller
{
      use AuthorizesRequests;
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
        $isAdmin = auth()->user()->role === 'admin';

        // Case 1: Admin → user_id من الـ Body
        // Case 2: Member → user_id من الـ Token
         $userId = $isAdmin
            ? $request->validated()['user_id']
            : auth()->id();

        // ✅ تحقق من التكرار للحالتين
        if (Member::where('user_id', $userId)->exists()) {
            return response()->json([
                'message' => $isAdmin
                    ? 'This user already has a member profile.'
                     : 'You already have a member profile.',
                    'status' => false
            ], 409);
        }

        $member = Member::create([
            ...$request->validated(),
            'user_id' => $userId,  // 👈 override دايماً لمنع التلاعب
        ]);

        $member->load('user');

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
    public function update(UpdateMemberRequest $request, member $member)
    {
        // we use patch method to update only the fields that are sent in the request, so we can use the same validation rules as store method instead use put method which requires all fields to be sent in the request



        //  $member = auth()->user()->member;

        // if (!$member) {
        //     return response()->json([
        //     'message' => 'Please complete your profile first.'
        //     ], 404);
        // }

            // allow admin
    if (auth()->user()->role !== 'admin' ){

        return response()->json([
        'message' => 'You are not allowed to access or modify this member.',
        'status' => false
    ], 403);
    }




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

    public function updateMe(UpdateMemberRequest $request)
    {
        $member = auth()->user()->member;

        if (!$member) {
            return response()->json([
                'message' => 'Please complete your profile first.',
                'status'  => false
            ], 404);
        }

        $member->update($request->validated());
         $member->load('user');

        return new MemberResource($member);
    }

    public function borrowings(Member $member)
    {
        $this->authorize('viewAny', [Borrowing::class, $member]);

        $borrowings = $member->borrowings()
            ->with(['book'])
            ->latest()
            ->paginate(10);

        return BorrowingResource::collection($borrowings);
    }

    public function fines(Member $member)
    {
        $this->authorize('viewAny', [Borrowing::class, $member]);

        $borrowings = $member->borrowings()
            ->withFines()
            ->with(['book'])
            ->get();

        $fines = $borrowings->map(fn($borrowing) => [
            'borrowing_id'  => $borrowing->id,
            'book'          => $borrowing->book->title,
            'due_date'      => $borrowing->due_date->toDateString(),
            'returned_date' => $borrowing->returned_date?->toDateString(),
            'overdue_days'  => $borrowing->getOverdueDays(),
            'fine_amount'   => $borrowing->calculateFine(),
            'status'        => $borrowing->status,
        ]);

        return response()->json([
            'status'     => true,
            'member'     => $member->name,
            'total_fine' => round($fines->sum('fine_amount'), 2),
            'currency'   => 'USD',
            'fines'      => $fines,
        ]);
    }


    public function suspend(Request $request, Member $member)
    {
       // $this->authorize('suspend', $member);

        if ($member->isSuspended()) {
            return response()->json([
                'status'  => false,
                'message' => 'Member is already suspended.',
            ], 422);
        }

        $member->suspend();

        return response()->json([
            'status'  => true,
            'message' => 'Member suspended successfully.',
        ]);
    }

}
