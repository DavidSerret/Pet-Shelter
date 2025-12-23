<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Mail\BlogNewsletter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NewsletterService
{
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->fromEmail = config('mail.from.address');
        $this->fromName = config('mail.from.name');
    }

    /**
     * Send newsletter immediately
     */
    public function sendImmediately(BlogPost $post): bool
    {
        try {
            if (!$this->canSendNewsletter($post)) {
                Log::warning('Cannot send newsletter for post: ' . $post->title);
                return false;
            }

            // Get subscribers
            $subscribers = $this->getSubscribers();
            
            if (empty($subscribers)) {
                Log::info('No subscribers to send newsletter to for post: ' . $post->title);
                
                // Mark as sent anyway (empty newsletter)
                $post->update([
                    'newsletter_sent_at' => now(),
                    'newsletter_scheduled_at' => null,
                ]);
                
                return true;
            }

            $sentCount = 0;
            foreach ($subscribers as $subscriber) {
                if ($this->sendToSubscriber($post, $subscriber)) {
                    $sentCount++;
                }
            }

            // Update post as sent
            $post->update([
                'newsletter_sent_at' => now(),
                'newsletter_scheduled_at' => null,
            ]);

            Log::info("Newsletter sent to {$sentCount} subscribers for post: " . $post->title);
            
            return $sentCount > 0;
            
        } catch (\Exception $e) {
            Log::error('Failed to send newsletter: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send newsletter to a single subscriber
     */
    protected function sendToSubscriber(BlogPost $post, $subscriber): bool
    {
        try {
            // Use Laravel Mail with SMTP
            Mail::to($subscriber)->send(new BlogNewsletter($post));
            
            Log::debug('Newsletter sent to: ' . $subscriber);
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to send to subscriber ' . $subscriber . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Schedule newsletter for later
     */
    public function schedule(BlogPost $post, \DateTime $scheduledAt): bool
    {
        try {
            $post->update([
                'newsletter_scheduled_at' => $scheduledAt,
                'newsletter_sent_at' => null,
            ]);
            
            Log::info('Newsletter scheduled for ' . $scheduledAt->format('Y-m-d H:i:s') . ' for post: ' . $post->title);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to schedule newsletter: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send scheduled newsletters that are due
     */
    public function sendScheduledNewsletters(): int
    {
        $duePosts = BlogPost::where('send_to_newsletter', true)
            ->whereNotNull('newsletter_scheduled_at')
            ->where('newsletter_scheduled_at', '<=', now())
            ->whereNull('newsletter_sent_at')
            ->get();
        
        $sentCount = 0;
        
        foreach ($duePosts as $post) {
            if ($this->sendImmediately($post)) {
                $sentCount++;
            }
        }
        
        return $sentCount;
    }

    /**
     * Get subscribers (placeholder - implement your own subscriber system)
     */
    protected function getSubscribers(): array
    {
        // For development/testing, add your own email
        $testEmails = [
            // 'tuemail@gmail.com', // Descomenta y agrega tu email para testing
        ];
        
        // Check if we have any test emails
        if (!empty($testEmails)) {
            return $testEmails;
        }
        
        // Check if subscribers table exists
        if (\Schema::hasTable('newsletter_subscribers')) {
            return \DB::table('newsletter_subscribers')
                ->where('is_active', true)
                ->pluck('email')
                ->toArray();
        }
        
        return [];
    }

    /**
     * Get subscribers count
     */
    public function getSubscribersCount(): int
    {
        return count($this->getSubscribers());
    }

    /**
     * Check if a post can be sent to newsletter
     */
    public function canSendNewsletter(BlogPost $post): bool
    {
        return $post->is_published && 
               $post->published_at && 
               !$post->newsletter_sent_at &&
               $post->send_to_newsletter;
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection(): bool
    {
        try {
            // Simple test - try to send a test email
            Mail::raw('Test email from Pet Shelter Newsletter Service', function ($message) {
                $message->to('test@example.com')
                        ->subject('Test Email - Newsletter Service')
                        ->from($this->fromEmail, $this->fromName);
            });
            
            Log::info('SMTP test successful');
            return true;
            
        } catch (\Exception $e) {
            Log::error('SMTP test failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add a test subscriber
     */
    public function addTestSubscriber(string $email): bool
    {
        try {
            // For now, just log - implement database later
            Log::info('Test subscriber added: ' . $email);
            
            // TODO: Implement database storage
            // NewsletterSubscriber::create([
            //     'email' => $email,
            //     'is_active' => true,
            //     'subscribed_at' => now(),
            // ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to add test subscriber: ' . $e->getMessage());
            return false;
        }
    }
}