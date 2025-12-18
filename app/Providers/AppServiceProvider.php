<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\AdoptionRequest;
use App\Models\Pet;
use App\Policies\AdoptionRequestPolicy;
use App\Policies\PetPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies for authorization
        Gate::policy(AdoptionRequest::class, AdoptionRequestPolicy::class);
        Gate::policy(Pet::class, PetPolicy::class);
    }
}
