<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    /**
     * Read the token through config() (not env()) so it keeps working
     * after `php artisan config:cache` in production.
     */
    private function token(): ?string
    {
        return config('services.telegram.bot_token') ?: null;
    }

    public function sendMessage(?string $chatId, string $text): void
    {
        $token = $this->token();

        if (!$token || !$chatId) {
            return;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if ($response->failed()) {
                Log::warning('Telegram sendMessage failed', ['chat_id' => $chatId, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram sendMessage error: ' . $e->getMessage(), ['chat_id' => $chatId]);
        }
    }

    /**
     * Send PDF report to Telegram
     */
    public function sendDocument(?string $chatId, string $fileAbsolutePath, string $caption = ''): void
    {
        $token = $this->token();

        if (!$token || !$chatId) {
            return;
        }

        if (!is_file($fileAbsolutePath)) {
            return;
        }

        try {
            $response = Http::timeout(20)
                ->attach('document', file_get_contents($fileAbsolutePath), basename($fileAbsolutePath))
                ->post("https://api.telegram.org/bot{$token}/sendDocument", [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]);

            if ($response->failed()) {
                Log::warning('Telegram sendDocument failed', ['chat_id' => $chatId, 'status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Throwable $e) {
            Log::warning('Telegram sendDocument error: ' . $e->getMessage(), ['chat_id' => $chatId]);
        }
    }
}
