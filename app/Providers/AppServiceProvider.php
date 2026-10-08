<?php

namespace App\Providers;

use Anthropic\Client;
use Anthropic\ServiceContracts\MessagesContract;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function (): Client {
            $apiKey = config('services.anthropic.api_key');

            if (! $apiKey) {
                throw new RuntimeException('Claude API key not specified in config/services.php (.env CLAUDE_API_KEY)');
            }

            return new Client(apiKey: $apiKey);
        });

        $this->app->bind(MessagesContract::class, fn ($app): MessagesContract => $app->make(Client::class)->messages);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
