<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        if (BlogPost::count() > 0) {
            return;
        }

        $admin = User::where('email', 'admin@happinest.com')->first();

        $posts = [
            [
                'title' => 'How to Prepare Your Home for a New Pet',
                'description' => 'Before your new companion arrives, a little preparation goes a long way. Here\'s everything you need to know to make the transition smooth for both of you.',
                'content' => '<p>Bringing a new pet home is one of the most exciting experiences — but it requires some planning. Whether you\'re adopting a dog, cat, or an exotic animal, making your space safe and welcoming is the first step.</p><h2>Create a Safe Space</h2><p>Designate a quiet corner with a bed or blanket where your pet can retreat when overwhelmed. New environments are stressful for animals, and having a personal space helps them settle in faster.</p><h2>Remove Hazards</h2><p>Check your home for common dangers: loose electrical cables, toxic plants (like lilies for cats), small objects that could be swallowed, and open trash cans. Get down to your pet\'s level and look for risks from their perspective.</p><h2>Stock Up on Essentials</h2><p>Before day one, have these ready: food and water bowls, appropriate food for their species and age, a collar and ID tag, a carrier or crate, toys for mental stimulation, and grooming supplies.</p><h2>Prepare the Family</h2><p>If you have children, set ground rules about handling the pet gently and giving them space to rest. If you have other pets, plan a gradual introduction to avoid territorial conflict.</p><p>With these steps in place, your new family member will feel at home in no time.</p>',
                'featured_image' => null,
                'is_published' => true,
            ],
            [
                'title' => 'Understanding Pet Adoption: What to Expect',
                'description' => 'Adoption is a life-changing decision for you and your pet. We walk you through the process, from the first visit to the first months at home.',
                'content' => '<p>Adopting a pet from a shelter is one of the most rewarding things you can do. You\'re giving an animal a second chance — and gaining a loyal companion for years to come. But it helps to go in with realistic expectations.</p><h2>The Adjustment Period</h2><p>Most pets need 3 days to decompress, 3 weeks to learn your routine, and 3 months to feel truly at home. Don\'t be discouraged if your new pet seems shy, anxious, or even misbehaves at first — this is completely normal.</p><h2>The First Vet Visit</h2><p>Schedule a vet appointment within the first week. Even if your pet came with a health certificate, a baseline check-up is essential. Your vet can also advise on diet, vaccinations, and parasite prevention specific to your region.</p><h2>Building Trust</h2><p>Let your pet set the pace for bonding. Offer treats, speak softly, and avoid forcing interaction. Patience is the most valuable tool in the first weeks.</p><h2>Training Basics</h2><p>Start with simple commands and positive reinforcement. Consistency matters more than duration — short, daily training sessions beat sporadic marathon sessions every time.</p><p>Remember: every adopted pet is different. Some warm up in days, others take months. The love you invest will always come back to you.</p>',
                'featured_image' => null,
                'is_published' => true,
            ],
            [
                'title' => 'Top Tips for First-Time Pet Owners',
                'description' => 'Never had a pet before? Don\'t worry. These practical tips will help you build a happy, healthy relationship with your new companion from day one.',
                'content' => '<p>Becoming a first-time pet owner is thrilling — and a little overwhelming. Here are the most important lessons experienced pet owners wish they\'d known from the start.</p><h2>1. Research Before You Commit</h2><p>Every species — and every breed — has different needs. A Border Collie needs hours of daily exercise. A senior cat is happy with gentle play and quiet company. Match the pet to your actual lifestyle, not the lifestyle you wish you had.</p><h2>2. Budget for the Full Picture</h2><p>The adoption fee is just the beginning. Factor in food, vet care (including unexpected emergencies), grooming, toys, and boarding when you travel. Pet insurance is worth exploring early.</p><h2>3. Routine Is Everything</h2><p>Animals thrive on predictability. Feed, walk, and play at consistent times each day. A stable routine reduces anxiety and unwanted behaviors significantly.</p><h2>4. Socialization Matters</h2><p>Expose your pet to different people, sounds, and environments early. Well-socialized pets are calmer, more confident, and easier to manage in new situations.</p><h2>5. Ask for Help</h2><p>Connect with your local vet, trainer, or shelter. There\'s no such thing as a silly question when it comes to your pet\'s wellbeing. Our team at Happinest is always here to support you even after adoption.</p><p>You\'ve got this. And your new pet is lucky to have you.</p>',
                'featured_image' => null,
                'is_published' => true,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::create([
                'title' => $post['title'],
                'slug' => Str::slug($post['title']),
                'description' => $post['description'],
                'content' => $post['content'],
                'featured_image' => $post['featured_image'],
                'is_published' => $post['is_published'],
                'published_at' => now(),
                'user_id' => $admin->id,
            ]);
        }
    }
}
