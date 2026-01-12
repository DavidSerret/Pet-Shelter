<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;

class BlogController extends Controller
{
    /**
     * Display public listing of blog posts
     */
    public function index()
    {
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->paginate(9);
            
        return view('blog.index', compact('posts'));
    }

    /**
     * Display single blog post publicly
     */
    public function show(BlogPost $post)
    {
        if (!$post->is_published && !Auth::user()?->isAdmin()) {
            abort(404);
        }
        
        if (!$post->published_at && !Auth::user()?->isAdmin()) {
            abort(404);
        }
        
        return view('blog.show', compact('post'));
    }

    /**
     * Display admin listing of all blog posts
     */
    public function adminIndex()
    {
        $posts = BlogPost::latest()->paginate(15);
        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Show form to create new blog post
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Upload image to ImgBB
     */
    private function uploadToImgBB($imageFile)
    {
        $client = new Client();
        
        try {
            // Public ImgBB API key (works for basic use)
            // Get your own free key at https://api.imgbb.com/
            $apiKey = '0bf01c7bd9d48bc8a15eb125ff654461';
            
            $response = $client->post('https://api.imgbb.com/1/upload', [
                'multipart' => [
                    [
                        'name' => 'key',
                        'contents' => $apiKey
                    ],
                    [
                        'name' => 'image',
                        'contents' => fopen($imageFile->getPathname(), 'r'),
                        'filename' => $imageFile->getClientOriginalName()
                    ]
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            if ($data['success']) {
                return $data['data']['url'];
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('ImgBB upload error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store new blog post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published' => 'boolean',
        ]);

        // Handle image upload
        $imageUrl = null;
        if ($request->hasFile('featured_image')) {
            $imageUrl = $this->uploadToImgBB($request->file('featured_image'));
            
            // If upload fails, use placeholder
            if (!$imageUrl) {
                $imageUrl = 'https://i.ibb.co/0jqWWpN/blog-placeholder.jpg';
            }
        }

        $post = BlogPost::create([
            'title' => strip_tags($validated['title']),
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'description' => strip_tags($validated['description']),
            'content' => $validated['content'],
            'featured_image' => $imageUrl,
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post ' . ($post->is_published ? 'published' : 'saved as draft') . ' successfully!');
    }

    /**
     * Show form to edit blog post
     */
    public function edit(BlogPost $post)
    {
        return view('admin.blog.edit', compact('post'));
    }

    /**
     * Update blog post
     */
    public function update(Request $request, BlogPost $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_published' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('featured_image')) {
            $imageUrl = $this->uploadToImgBB($request->file('featured_image'));
            
            if ($imageUrl) {
                $validated['featured_image'] = $imageUrl;
            } else {
                // If upload fails, keep current image
                $validated['featured_image'] = $post->featured_image;
            }
        } else {
            // Keep existing image
            $validated['featured_image'] = $post->featured_image;
        }

        // Handle publish status
        $isPublishing = ($validated['is_published'] ?? false) && !$post->is_published;
        $isUnpublishing = !($validated['is_published'] ?? false) && $post->is_published;

        $updateData = [
            'title' => strip_tags($validated['title']),
            'slug' => $validated['slug'] ?? $post->slug,
            'description' => strip_tags($validated['description']),
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'],
            'is_published' => $validated['is_published'] ?? false,
        ];

        // Update published_at based on status changes
        if ($isPublishing) {
            $updateData['published_at'] = now();
        } elseif ($isUnpublishing) {
            $updateData['published_at'] = null;
        } else {
            $updateData['published_at'] = $post->published_at;
        }

        $post->update($updateData);

        $statusMessage = $isPublishing ? 'published' : ($isUnpublishing ? 'unpublished and saved as draft' : 'updated');
        return redirect()->route('admin.blog.index')
            ->with('success', "Blog post {$statusMessage} successfully!");
    }

    /**
     * Delete blog post
     */
    public function destroy(BlogPost $post)
    {
        $post->delete();
        
        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully!');
    }
}