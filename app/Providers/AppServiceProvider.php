<?php

namespace App\Providers;

use Anthropic\Client;
use Anthropic\ServiceContracts\MessagesContract;
use App\Actions\Auth\GeneratePasskeyRegisterOptions;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;

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

        // Spatie's action omits pubKeyCredParams, which browsers reject; see GeneratePasskeyRegisterOptions.
        $this->app->bind(GeneratePasskeyRegisterOptionsAction::class, GeneratePasskeyRegisterOptions::class);

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
