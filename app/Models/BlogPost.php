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
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
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
            return asset('assets/blog-placeholder.jpg');
        }
        
        if (str_starts_with($this->featured_image, 'data:image')) {
            return $this->featured_image;
        }
        
        if (base64_encode(base64_decode($this->featured_image, true)) === $this->featured_image) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, base64_decode($this->featured_image));
            finfo_close($finfo);
            
            return 'data:' . $mimeType . ';base64,' . $this->featured_image;
        }
        
        return $this->featured_image;
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
}