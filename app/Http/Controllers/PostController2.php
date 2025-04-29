<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StorePostRequest;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class PostController2 extends Controller
{
    // Show the form for creating a new post
    public function create()
    {
        return inertia('Post/Create');
    }

    // Store a new post with media
    public function store(StorePostRequest $request)
    {

        // Create the post
        $post = auth()->user()->posts()->create($request->only([
            'title', 'body',
        ]));

        // Handle media file uploads
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                $filePath = $file->store('posts', 'public'); // Store in storage/app/public/posts
                $post->mediaFiles()->create([
                    'file_type' => $file->getClientMimeType(),
                    'file_path' => $filePath,
                ]);
            }
        }

        return redirect()->route('posts.index')->with('success', 'Post created successfully!');
    }

    // List all posts
    public function index()
    {
        $posts = Post::with('mediaFiles')->get();
        return inertia('Post/Index', [
            'posts' => $posts,
        ]);
    }

    // Delete a post
    public function destroy(Post $post)
    {
        // Delete associated media files
        foreach ($post->mediaFiles as $mediaFile) {
            Storage::disk('public')->delete($mediaFile->file_path);
            $mediaFile->delete();
        }

        // Delete the post
        $post->delete();

        return redirect()->back()->with('success', 'Post deleted successfully!');
    }
}