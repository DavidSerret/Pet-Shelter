<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsletterService;

class SendScheduledNewsletters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletters:send-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled newsletters that are due';

    /**
     * Execute the console command.
     */
    public function handle(NewsletterService $newsletterService)
    {
        $this->info('Checking for scheduled newsletters...');
        
        $sentCount = $newsletterService->sendScheduledNewsletters();
        
        if ($sentCount > 0) {
            $this->info("Successfully sent {$sentCount} scheduled newsletter(s).");
        } else {
            $this->info('No scheduled newsletters to send at this time.');
        }
        
        return Command::SUCCESS;
    }
}