<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Fix for DomPDF public path resolution in production
        // This ensures DomPDF can resolve the public path correctly
        // Handles both 'public' (standard Laravel) and 'public_html' (shared hosting like Hostinger)
        $this->app->bind('path.public', function() {
            // Check if public_html exists (common on shared hosting like Hostinger)
            if (is_dir(base_path('public_html'))) {
                return base_path('public_html');
            }
            // Fall back to standard 'public' directory
            return base_path('public');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set DomPDF public path explicitly if config exists
        // Handles both 'public' and 'public_html' directories
        if (Config::has('dompdf.public_path')) {
            $publicPath = is_dir(base_path('public_html')) 
                ? base_path('public_html') 
                : base_path('public');
            Config::set('dompdf.public_path', $publicPath);
        }
    }
}
