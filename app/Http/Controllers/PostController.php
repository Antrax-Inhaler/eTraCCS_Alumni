<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\MediaFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::withCount('reactions')->get();
        return Inertia::render('Posts/Index', ['posts' => $posts]);
    }

    public function create()
    {
        return Inertia::render('Posts/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'media_files' => 'nullable|array', // Validate as an array
            'media_files.*' => 'file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:10240', // Validate each file
        ]);
    
        $post = Post::create([
            'title' => $request->title,
            'body' => $request->body,
        ]);
    
        // Debug: Check if files exist in the request
        Log::info('Media Files Received: ', $request->file('media_files') ? $request->file('media_files') : []);
    
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                // Debug: Log file info
                Log::info('Processing file: ', [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                    'tmp_path' => $file->getPathname(),
                ]);
    
                $filePath = $file->store('public/media');
    
                if ($filePath) {
                    Log::info('File stored successfully: ' . $filePath);
                    MediaFile::create([
                        'post_id' => $post->id,
                        'file_type' => $file->getClientMimeType(),
                        'file_path' => str_replace('public/', '', $filePath),
                    ]);
                } else {
                    Log::error('Failed to store file: ' . $file->getClientOriginalName());
                }
            }
        } else {
            Log::warning('No media files found in the request.');
        }
    
        return redirect()->route('posts.index')->with('success', 'Post created successfully.');
    }
    

    public function edit(Post $post)
    {
        return Inertia::render('Posts/Edit', ['post' => $post]);
    }

    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $post->update($request->all());

        return redirect()->route('posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('posts.index')->with('success', 'Post deleted successfully.');
    }

    // ✅ New method to handle reactions
    public function react(Request $request, Post $post)
    {
        $request->validate([
            'reaction_type' => 'required|integer|min:1|max:5',
        ]);

        Reaction::create([
            'post_id' => $post->id,
            'reaction_type' => $request->reaction_type,
        ]);

        return response()->json(['reactions_count' => $post->reactions()->count()]);
    }
}
