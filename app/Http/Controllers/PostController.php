<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\AuthToken;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Post::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:posts|max:64',
            'content' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                "message" => $validator->messages()
            ], 422);
        }

        $access_token = $request->bearerToken();
        $user_id = AuthToken::where('access_token', $access_token)->value('user_id');
        $post = [
            'user_id' => $user_id,
            'title' => $request->title,
            'content' => $request->content,
        ];

        $post = Post::create($post);
        
        return response()->json([
            'post_id' => $post->id
        ], 201);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::find($id);

        if(!$post) {
            return response()->json([
                "message" => "This Post does not exist, check your details"
            ], 404);
        }

        return response()->json($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);

        if(!$post) {
            return response()->json([
                "message" => "This Post does not exist, check your details"
            ], 404);
        }

        if($request->title) {
            $validator = Validator::make($request->all(), [
                'title' => 'max:64|unique:posts,title,' . $id,
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    "message" => $validator->messages()
                ], 422);
            }
            
            $post->title = $request->title;
        }

        if($request->content) {
            $post->content = $request->content;
        }

        $post->save();

        return response()->json([
            "message" =>"Successfully update"
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        
        if(!$post) {
            return response()->json([
                "message" => "This Post does not exist, check your details"
            ], 404);
        }

        Post::destroy($id);

        return response()->json([
            "message" =>"Successfully removed"
        ], 200);
    }
}
