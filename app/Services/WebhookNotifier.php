<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookNotifier
{
    /**
     * Post a message to every configured chat webhook.
     *
     * The message is written in Slack mrkdwn (`*bold*`); it is converted to
     * Discord markdown (`**bold**`) for the Discord webhook. Failures are
     * logged and never thrown.
     */
    public function notify(string $message): void
    {
        $this->post('Discord', config('services.discord.webhook_url'), ['content' => str_replace('*', '**', $message)]);
        $this->post('Slack', config('services.slack.webhook_url'), ['text' => $message]);
    }

    /**
     * @param  array<string, string>  $payload
     */
    private function post(string $service, ?string $url, array $payload): void
    {
        if (! $url) {
            return;
        }

        try {
            $response = Http::post($url, $payload);

            if ($response->failed()) {
                Log::error("{$service} webhook request failed with status {$response->status()}.");
            }
        } catch (Throwable $e) {
            Log::error("Error dispatching {$service} webhook: ".$e->getMessage());
        }
    }
}
