<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{


    /**
     * Display a listing of the resource.
     */


    public function store(Request $request, Article $article)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        $data['article_id'] = $article->id;

        $comment = Comment::create($data);
        $comment->load('user:id,name');

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment created successfully',
            'data'    => $comment
        ], 201);
    }


    public function update(Request $request, string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('update', $comment);

        $validator = Validator::make($request->all(), [
            'content' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $data = $validator->validated();
        $data['user_id'] = auth()->id();

        $comment->update($data);
        $comment->refresh(); 

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment
        ]);
    }

    public function destroy(string $id)
    {
        $comment = Comment::findOrFail($id);
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Comment deleted successfully'
        ]);
    }
    
}
