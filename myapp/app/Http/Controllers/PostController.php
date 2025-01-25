<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware for the resource.
     *
     * @return array
     */    
    public static function middleware()
    {
        return[
            new middleware('auth:sanctum', except:['index', 'show'])
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Post::with(['user'])->latest()->get();
    }

    
    /**
     * Store a newly created post in storage.
     *
     * @param Request $request The incoming request containing post data.
     * 
     * @return array An array containing the newly created post and its associated user.
     */
    public function store(Request $request)
    {
        
        $fields = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate the image
        ]);
       
        // Handle the image file if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public'); // Save to 'storage/app/public/images'
            $fields['image'] = $imagePath;
        }

        // Create the post with the current user
        $post = $request->user()->Post()->create($fields);

        return [
            'post' => $post,
            'user' => $post->user
        ];
    } 

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return [
            'post' => $post,
            'user' => $post->user
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        // check if the user can modify the post using the authorize method
        Gate::authorize('modify',$post);

        $fields = $request->validate([
            'title' => 'required|max:255',
            'body' => 'required',
        ]);

        $post->update($fields);

        return [
            'post' => $post,
            'user' => $post->user
        ];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        // check if the user can modify the post using the authorize method
        Gate::authorize('modify',$post);
        
        $post->delete();
         return ["messege" => "Post deleted"];
    }
}
