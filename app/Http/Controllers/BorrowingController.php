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

        //  if($request->has('search')) {

        //     $search = $request->search;

        //     $query->where(function ($q) use ($search) {

        //         $q->whereHas('book', function ($bookQuery) use ($search) {
        //             $bookQuery->where('title', 'like', "%{$search}%");
        //         })->orWhereHas('member', function ($memberQuery) use ($search) {
        //             $memberQuery->where('name', 'like', "%{$search}%");
        //         });
        //     });


        $borrowings= $query->latest()->paginate(10);
        return BorrowingResource::collection($borrowings);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBorrowingRequest $request)
    {
        //
        $book= Book::find($request->book_id);
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
             $borrowing->load(['book', 'member']);
             return new BorrowingResource($borrowing);
         } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
             return response()->json([
                 'status' => false,
                 'message' => 'Borrowing record not found'], 404);
         }
     }


        public function returnBook(Borrowing $borrowing)
        {
            if ($borrowing->status !== 'borrowed') {
                return response()->json([
                    'status' => false,
                    'message' => 'This book is not currently borrowed'], 422);
            }

            $borrowing->update([
                'returned_date' => now(),
                'status' => 'returned',
            ]);

            $borrowing->book->returnBook();
            $borrowing->load(['book', 'member']);

            return new BorrowingResource($borrowing);
        }

        public function overdue()
        {
            $overdueBorrowings = Borrowing::with(['book', 'member'])
                ->where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->get();

                Borrowing::where('status', 'borrowed')
                ->where('due_date', '<', now())
                ->update(['status' => 'overdue']);
                //->latest()->paginate(10);

            return BorrowingResource::collection($overdueBorrowings);
        }
}
