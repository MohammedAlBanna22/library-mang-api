<?php

namespace App\Http\Controllers;

use App\Enums\BorrowingStatus;
use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
      use AuthorizesRequests;
    public function index(Request $request)
    {
        //
        $user = auth()->user();

        $this->authorize('viewAny', Borrowing::class);

       $query = Borrowing::query()->with(['book', 'member.user']);
        if ($user->role === 'member') {
            $query->where('member_id', $user->member->id);
        }
        if (request()->has('status')) {

            $query->where('status', request()->status);

        }


        if($request->has('member_id')){

            $query->where('member_id', $request->member_id);
        }



        $borrowings= $query->latest()->paginate(10);
        return BorrowingResource::collection($borrowings);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBorrowingRequest $request)
    {
        //
        $member = auth()->user()->member;
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        if ($isAdmin) {
            $member = Member::findOrFail($request->member_id);
        } else {
            $member = $user->member;

            if (!$member) {
                return response()->json([
                'message' => 'You must be member .',
                'status'  => false
            ], 403);
            }
        }
        $book= Book::findOrFail($request->book_id);
        if(!$book || !$book->isAvailable()){

            return response()->json([
                'status' => false,
                'message' => 'Book is not available for borrowing'], 422);
        }


        $borrowing = DB::transaction(function () use ($request, $member, $book) {
            $book->borrow();

            return Borrowing::create([
                ...$request->validated(),
                'member_id' => $member->id,
                'status'    => 'borrowed',
            ]);
        });

        $borrowing->load(['book', 'member']);
        return new BorrowingResource($borrowing);

    }

    /**
     * Display the specified resource.
     */
    public function show($id)//use id not model binding to handle not found exception
     {
         //
         try {
             $borrowing = Borrowing::with(['book', 'member'])->findOrFail($id);
             return new BorrowingResource($borrowing);
         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json([
                 'status' => false,
                 'message' => 'Borrowing record not found'], 404);
         }
     }


    public function returnBook(Borrowing $borrowing)
    {
            $this->authorize('returnBook', $borrowing);
            if (!$borrowing->canBeReturned()) {
                return response()->json([
                'status'  => false,
                'message' => 'This book cannot be returned'
            ], 422);
            }

            DB::transaction(function () use ($borrowing) {
                $borrowing->update([
                    'returned_date' => now(),
                    'status'        => 'returned',
                ]);

                $borrowing->book->returnBook();
            });

            $borrowing->load(['book', 'member']);

            return new BorrowingResource($borrowing);
    }
//check overdue borrowing and update status to overdue

    public function overdue()
    {
        $overdueBorrowings = Borrowing::overdue()
                ->with(['book', 'member'])
                 ->latest()
                ->paginate(10);

        return BorrowingResource::collection($overdueBorrowings);
    }

    public function overdueStatus()
    {
        $count = Borrowing::markAllOverdue();

        return response()->json([
            'status'  => true,
            'message' => "$count borrowing(s) marked as overdue",
            'count'   => $count
        ]);
    }

     public function borrowingHistory(Book $book)
    {
        $this->authorize('viewAny', Borrowing::class);

        $history = $book->borrowings()
                    ->with(['member.user'])
                    ->latest()
                    ->get();

        if ($history->isEmpty()) {
            return response()->json([
                'message' => 'No borrowing history found.',
                'status'  => false
            ], 404);
        }

        return response()->json([
            'book'    => [
                'id'    => $book->id,
                'title' => $book->title,
                'book_isbn' => $book->isbn,
            ],
            'history' => BorrowingResource::collection($history),
            'total'   => $history->count(),
        ]);
    }

    public function renew(Borrowing $borrowing)
    {
        $this->authorize('renew', $borrowing);

        if (!$borrowing->canBeRenewed()) {
            return response()->json([
                'message' => $this->getRenewalErrorMessage($borrowing),
                'status'  => false
            ], 422);
        }

        $borrowing->update([
            'due_date'      => now()->addDays(14),
            'renewal_count' => $borrowing->renewal_count + 1,
            ]);

        $borrowing->load(['book', 'member']);

        return new BorrowingResource($borrowing);
    }

    private function getRenewalErrorMessage(Borrowing $borrowing): string
    {
        if ($borrowing->renewal_count >= Borrowing::MAX_RENEWALS) {
            return 'Maximum renewals reached.';
        }

        if ($borrowing->status !== BorrowingStatus::Borrowed) {
            return 'Only active borrowings can be renewed.';
        }

        $daysUntilDue = now()->startOfDay()->diffInDays($borrowing->due_date->startOfDay(), false);

        if ($daysUntilDue > 3) {
            $daysLeft = (int) $daysUntilDue;
            return "Renewal not available yet. You can renew in last 3 days of {$daysLeft} days.";
        }

        if ($daysUntilDue < 0) {
         return 'Borrowing is overdue and cannot be renewed.';
        }

        return 'This borrowing cannot be renewed.';
    }


    public function fine(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        return response()->json([
            'status'        => true,
            'is_overdue'    => $borrowing->isOverdue(),
            'overdue_days'  => $borrowing->getOverdueDays(),
            'fine_amount'   => $borrowing->calculateFine(),
            'currency'      => 'USD',
            'due_date'      => $borrowing->due_date->toDateString(),
            'returned_date' => $borrowing->returned_date?->toDateString(),
        ]);
    }

    public function payFine(Borrowing $borrowing)
    {
        $this->authorize('view', $borrowing);

        if ($borrowing->calculateFine() === 0.0) {
            return response()->json([
                'status'  => false,
                'message' => 'No fine to pay.',
            ], 422);
        }

        if ($borrowing->isFinePaid()) {
            return response()->json([
                'status'  => false,
                'message' => 'Fine already paid.',
            ], 422);
        }

        $borrowing->payFine();

         return response()->json([
            'status'       => true,
            'message'      => 'Fine paid successfully.',
            'fine_amount'  => $borrowing->calculateFine(),
            'paid_amount'  => $borrowing->fine_amount,
            'currency'     => 'USD',
            'fine_paid_at' => $borrowing->fine_paid_at->toDateTimeString(),
        ]);
    }

}