<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Article;
use App\Models\Comment;
use App\Services\CommentService;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService) {}

    // POST /api/comments/articles/{article}
    public function store(StoreCommentRequest $request, Article $article)
    {
        $comment = $this->commentService->create(
            $article,
            auth()->id(),
            $request->validated('content')
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment created successfully',
            'data'    => new CommentResource($comment),
        ], 201);
    }

    // PUT /api/comments/{comment}
    public function update(UpdateCommentRequest $request, string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $comment = $this->commentService->update($comment, $request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment updated successfully',
            'data'    => new CommentResource($comment),
        ]);
    }

    // DELETE /api/comments/{comment}
    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment deleted successfully',
        ]);
    }
}
