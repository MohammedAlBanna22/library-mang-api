<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBorrowingRequest;
use App\Http\Resources\BorrowingResource;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Http\Request;

class BorrowingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = Borrowing::with(['book', 'member']);
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
        $book= Book::findOrFail($request->book_id);
        if(!$book || !$book->isAvailable()){

            return response()->json([
                'status' => false,
                'message' => 'Book is not available for borrowing'], 422);
        }
        $borrowing = Borrowing::create($request->validated());
        $book->borrow() ;
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
            // if ($borrowing->status !== 'borrowed') {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'This book is not currently borrowed'], 422);
            // }

            if (!$borrowing->canBeReturned()) {
                return response()->json([
                'status'  => false,
                'message' => 'This book cannot be returned'
            ], 422);
            }

            $borrowing->update([
                'returned_date' => now(),
                'status' => 'returned',
            ]);

            $borrowing->book->returnBook();
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
}
