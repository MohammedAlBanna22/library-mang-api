<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthorUpgradeRequest;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Resources\AuthorRequestResource;
use App\Models\Author;
use App\Models\AuthorRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthorRequestController extends Controller
{
    //
      // Member يشوف طلبه
    public function myRequest()
    {
        $request = AuthorRequest::where('user_id', auth()->id())
                                ->latest()
                                   ->get();

        if (!$request) {
            return response()->json([
                'message' => 'No request found.',
                'status'  => false
            ], 404);
        }

        return response()->json(['data' => $request]);
    }


    public function store(AuthorUpgradeRequest $request)
    {
 // تأكد ما هو author مسبقاً
    if (auth()->user()->role === 'author') {
        return response()->json([
            'message' => 'You are already an author.',
            'status'  => false
        ], 409);
    }

    // تأكد ما عنده طلب pending
    if (AuthorRequest::where('user_id', auth()->id())
                     ->where('status', 'pending')
                     ->exists()) {
        return response()->json([
            'message' => 'You already have a pending request.',
            'status'  => false
        ], 409);
    }

    $validated = $request->validated();

    $authorRequest = DB::transaction(function () use ($validated) {
       $author = Author::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                ...\Arr::except($validated, ['reason']),
                'status' => 'inactive',
            ]
        );
        return AuthorRequest::create([
            'user_id'   => auth()->id(),
            'author_id' => $author->id,
            'reason'    => $validated['reason'],
            'status'    => 'pending',
        ]);
    });

    $authorRequest->load(['user', 'author']);

    return new AuthorRequestResource($authorRequest);
    }


    public function index()
    {
        $requests = AuthorRequest::with(['user', 'author'])
                                 ->latest()
                                 ->get();

        return AuthorRequestResource::collection($requests);
    }

    // Admin يوافق
    public function approve(AuthorRequest $authorRequest)
    {
        if ($authorRequest->status !== 'pending') {
        return response()->json([
            'message' => 'Request already processed.',
            'status'  => false
        ], 409);
    }

    // 👈 فعّل الـ author فقط بدون ما تنسخ البيانات
    //$authorRequest->author->update(['status' => 'active']);
    // if ($authorRequest->author) {
    // $authorRequest->author->update(['status' => 'active']);
    // }

    // // غيّر الـ role
    // $authorRequest->user->update(['role' => 'author']);

    // // غيّر status الطلب
    // $authorRequest->update(['status' => 'approved']);



    DB::transaction(function () use ($authorRequest) {
        $authorRequest->author->update(['status' => 'active']);
        $authorRequest->user->update(['role' => 'author']);
        $authorRequest->update(['status' => 'approved']);
    });


    return response()->json([
        'message' => 'Author approved successfully.',
        'status'  => true
    ]);
    }

    // Admin يرفض
    public function reject(AuthorRequest $authorRequest)
    {
        if ($authorRequest->status !== 'pending') {
            return response()->json([
            'message' => 'Request already processed.',
            'status'  => false
            ], 409);
        }
         $authorRequest->load('author'); // 👈 حمّل العلاقة قبل الـ transaction

    // 👈 لو وحدة فشلت كلهم بيترجعوا
        DB::transaction(function () use ($authorRequest) {
                // 👈 احذف الـ author record نهائياً
            if ($authorRequest->author) {
                $authorRequest->author->delete();
            }
            $authorRequest->update(['status' => 'rejected']);
        });


        return response()->json([
            'message' => 'Request rejected Successfully.',
            'status'  => true
        ]);
    }
}
