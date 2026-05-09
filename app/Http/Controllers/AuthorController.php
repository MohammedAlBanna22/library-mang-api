<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;
use Nette\Schema\Message;

class AuthorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
        //
        $author= Author::create($request->validated());
        // return response()->json([
        //     '$author'=>$author,
        //     'message'=>'the author is stored',
        // ],200);
        return new AuthorResource($author);// use resource to show what data to display

    }

    /**
     * Display the specified resource.
     */
    public function show(Author $author)
    {
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
        $author->update($request->validated());
        return new AuthorResource($author);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Author $author)
    {
       // $author->delete();
    }
}
