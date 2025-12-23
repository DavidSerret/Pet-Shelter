<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

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
        // Ensure only published posts are visible to non-admins
        if (!$post->is_published && !Auth::user()?->isAdmin()) {
            abort(404);
        }
        
        // Also check published_at for consistency
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
     * Store new blog post
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,NULL,id,deleted_at,NULL',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
            'send_to_newsletter' => 'boolean',
            'newsletter_send_option' => 'nullable|in:now,schedule',
            'newsletter_scheduled_at' => 'nullable|date|after:now',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('blog-images', 'public');
        }

        // Handle newsletter scheduling
        $newsletterScheduledAt = null;
        $newsletterSentAt = null;
        
        if ($validated['send_to_newsletter'] ?? false) {
            if (($validated['newsletter_send_option'] ?? 'now') === 'schedule' && 
                isset($validated['newsletter_scheduled_at'])) {
                $newsletterScheduledAt = Carbon::parse($validated['newsletter_scheduled_at']);
            } else {
                // Send immediately - will be handled by job/queue
                $newsletterSentAt = null; // Will be set when actually sent
            }
        }

        $post = BlogPost::create([
            'title' => strip_tags($validated['title']),
            'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
            'description' => strip_tags($validated['description']),
            'content' => $validated['content'],
            'featured_image' => $imagePath,
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
            'send_to_newsletter' => $validated['send_to_newsletter'] ?? false,
            'newsletter_scheduled_at' => $newsletterScheduledAt,
            'newsletter_sent_at' => $newsletterSentAt,
            'user_id' => Auth::id(),
        ]);

        // Handle immediate newsletter sending (if requested)
        if ($post->isNewsletterImmediate()) {
            // We'll implement this later with the NewsletterService
            // NewsletterService::sendImmediately($post);
        }

        $successMessage = 'Blog post ' . ($post->is_published ? 'published' : 'saved as draft') . ' successfully!';
        
        if ($post->send_to_newsletter) {
            if ($post->newsletter_scheduled_at) {
                $scheduledDate = $post->newsletter_scheduled_at->format('M j, Y \a\t g:i A');
                $successMessage .= " Newsletter scheduled for {$scheduledDate}.";
            } else {
                $successMessage .= ' Newsletter will be sent to subscribers.';
            }
        }

        return redirect()->route('admin.blog.index')
            ->with('success', $successMessage);
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
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id . ',id,deleted_at,NULL',
            'description' => 'required|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_published' => 'boolean',
            'send_to_newsletter' => 'boolean',
            'newsletter_send_option' => 'nullable|in:now,schedule',
            'newsletter_scheduled_at' => 'nullable|date|after:now',
        ]);

        // Handle image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image if exists
            if ($post->featured_image) {
                Storage::disk('public')->delete($post->featured_image);
            }
            $imagePath = $request->file('featured_image')->store('blog-images', 'public');
            $validated['featured_image'] = $imagePath;
        } else {
            // Keep existing image
            $validated['featured_image'] = $post->featured_image;
        }

        // Handle publish status
        $isPublishing = ($validated['is_published'] ?? false) && !$post->is_published;
        $isUnpublishing = !($validated['is_published'] ?? false) && $post->is_published;

        // Handle newsletter scheduling
        $newsletterScheduledAt = $post->newsletter_scheduled_at;
        $newsletterSentAt = $post->newsletter_sent_at;
        
        if ($validated['send_to_newsletter'] ?? false) {
            // Only update if newsletter hasn't been sent yet
            if (!$post->newsletter_sent_at) {
                if (($validated['newsletter_send_option'] ?? 'now') === 'schedule' && 
                    isset($validated['newsletter_scheduled_at'])) {
                    $newsletterScheduledAt = Carbon::parse($validated['newsletter_scheduled_at']);
                    $newsletterSentAt = null;
                } elseif (($validated['newsletter_send_option'] ?? 'now') === 'now') {
                    $newsletterScheduledAt = null;
                    $newsletterSentAt = null; // Will be set when sent
                }
            }
        } else {
            // If unchecking newsletter, clear scheduling
            $newsletterScheduledAt = null;
        }

        $updateData = [
            'title' => strip_tags($validated['title']),
            'slug' => $validated['slug'] ?? $post->slug,
            'description' => strip_tags($validated['description']),
            'content' => $validated['content'],
            'featured_image' => $validated['featured_image'],
            'is_published' => $validated['is_published'] ?? false,
            'send_to_newsletter' => $validated['send_to_newsletter'] ?? false,
            'newsletter_scheduled_at' => $newsletterScheduledAt,
            'newsletter_sent_at' => $newsletterSentAt,
        ];

        // Update published_at based on status changes
        if ($isPublishing) {
            $updateData['published_at'] = now();
        } elseif ($isUnpublishing) {
            $updateData['published_at'] = null;
        } else {
            // Keep existing published_at if already published
            $updateData['published_at'] = $post->published_at;
        }

        $post->update($updateData);

        // Handle immediate newsletter sending (if requested and not already sent)
        if ($post->isNewsletterImmediate() && !$post->newsletter_sent_at) {
            // We'll implement this later with the NewsletterService
            // NewsletterService::sendImmediately($post);
        }

        $statusMessage = $isPublishing ? 'published' : ($isUnpublishing ? 'unpublished and saved as draft' : 'updated');
        
        if ($post->send_to_newsletter && !$post->newsletter_sent_at) {
            if ($post->newsletter_scheduled_at) {
                $scheduledDate = $post->newsletter_scheduled_at->format('M j, Y \a\t g:i A');
                $statusMessage .= ". Newsletter scheduled for {$scheduledDate}";
            } else {
                $statusMessage .= '. Newsletter will be sent to subscribers';
            }
        }

        return redirect()->route('admin.blog.index')
            ->with('success', "Blog post {$statusMessage} successfully!");
    }

    /**
     * Delete blog post
     */
    public function destroy(BlogPost $post)
    {
        // Delete image if exists
        if ($post->featured_image) {
            Storage::disk('public')->delete($post->featured_image);
        }
        
        $post->delete();
        
        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog post deleted successfully!');
    }
}