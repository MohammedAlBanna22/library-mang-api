<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Nette\Schema\Message;

class AuthorController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (auth()->user()->cannot('viewAny',  Author::class)) {
            return response()->json([
                'status' => false,
                'message' => 'Only admin can view authors list',
            ], 403);
        }
         $this->authorize('viewAny', Author::class);

        $authors = Author::with('books')->paginate(10);

        // return response()->json([
        //     'authors' => $authors,
        //     'message'=>'Authors Fetched with success ',
        // ], status: 200);

        return AuthorResource::collection($authors);// use resource to show what data to display

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAuthorRequest $request)// use storeauthorrequest to make custim valdiate
    {
        $this->authorize('create', Author::class);
        $author = Author::create([
            ...$request->validated(),
            'status'  => 'active',                          // 👈 Admin يضيف مباشرة active
        ]);

        return new AuthorResource($author);

    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {

        if (auth()->user()->cannot('view', $author)) {
        return response()->json([
            'status' => false,
            'message' => 'Only admin or owner can view this author',
         ], 403);
        }

        $this->authorize('view', $author);
        $author->load('user', 'books');
        // use Author route model binding to get the author by id and make it as collection to display
       // $author= Author::findOrFail($id);
       //laravel will automatically find the author by id and if not found it will throw 404 error
        return new AuthorResource($author);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAuthorRequest $request, Author $author)
    {
         $this->authorize('update', $author);

        $author->update($request->validated());
        return new AuthorResource($author);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
        $this->authorize('delete', $author); // 👈
        $author->user->update(['role' => 'member']);
        $author->delete();
        return response()->json([
            'message'=>'the author is deleted',
        ],200);
    }
    // Author يعدل نفسه
    public function updateMe(UpdateAuthorRequest $request)
    {
        $author = auth()->user()->author;

        if (!$author) {
            return response()->json([
                'message' => 'Author profile not found.',
                'status'  => false
            ], 404);
        }

        $this->authorize('update', $author);

        $author->update($request->validated());
        return new AuthorResource($author);
    }
}
