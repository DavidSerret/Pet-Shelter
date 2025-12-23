<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'featured_image',
        'is_published',
        'published_at',
        'user_id',
        'send_to_newsletter',        // Nuevo
        'newsletter_scheduled_at',   // Nuevo
        'newsletter_sent_at',        // Nuevo
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'send_to_newsletter' => 'boolean',          // Nuevo
        'newsletter_scheduled_at' => 'datetime',    // Nuevo
        'newsletter_sent_at' => 'datetime',         // Nuevo
    ];

    /**
     * Get the user that created the blog post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        
        return asset('storage/' . $this->featured_image);
    }

    /**
     * Generate slug from title before creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = \Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = \Str::slug($post->title);
            }
        });
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at');
    }

    /**
     * Scope for draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('is_published', false)
            ->orWhereNull('published_at');
    }

    /**
     * Scope for posts that should be sent to newsletter.
     */
    public function scopeForNewsletter($query)
    {
        return $query->where('send_to_newsletter', true)
            ->whereNull('newsletter_sent_at');
    }

    /**
     * Check if newsletter is scheduled.
     */
    public function isNewsletterScheduled(): bool
    {
        return $this->send_to_newsletter && 
               $this->newsletter_scheduled_at && 
               !$this->newsletter_sent_at;
    }

    /**
     * Check if newsletter should be sent immediately.
     */
    public function isNewsletterImmediate(): bool
    {
        return $this->send_to_newsletter && 
               !$this->newsletter_scheduled_at && 
               !$this->newsletter_sent_at;
    }

    /**
     * Check if newsletter has been sent.
     */
    public function isNewsletterSent(): bool
    {
        return (bool) $this->newsletter_sent_at;
    }
}