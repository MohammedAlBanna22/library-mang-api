<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request)
    {
        // $books= Book::with('author')->paginate(10);
        // return BookResource::collection($books);

        $query = Book::with('author');

        // search functionality
        if ($request->has('search')) {

            $search = $request->search;

            $query->where(function (Builder $q) use ($search): void {

                $q->where('title', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhereHas('author', function (Builder $authorQuery) use ($search) {

                     $authorQuery->where('name', 'like', "%{$search}%");

                });
            });
        }

        if($request->has('genre')){

            $query->where('genre', $request->genre);
        }

        $books = $query->paginate(10);

        return BookResource::collection($books);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        //
        $book= Book::create($request->validated());
        $book->load('author');
        return new BookResource($book);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        try {
            $book = Book::findOrFail($id);
            $book->load('author');
            return new BookResource($book);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Book not found'], 404);
        }

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        //
        $book->update($request->validated());
        $book->load('author');
        return new BookResource($book);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // //
        // $book->delete();

        // return response()->json([
        //     'status' => true,
        //     'message' => 'Book deleted successfully'], 200);//204 no content


            try {
                $book = Book::findOrFail($id);
                  $book->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Book deleted successfully'], 200);

            } catch (ModelNotFoundException $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Book not found'], 404);
             }

    }
}
