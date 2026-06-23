<?php
// LOCATION: app/Services/TelegramService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $token;
    protected ?string $chatId;

    public function __construct()
    {
        $this->token  = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    /**
     * Send a plain text message to the configured admin chat.
     * Fails silently (logs the error) so a Telegram outage never breaks
     * the actual business action (deposit approval, registration, etc).
     */
    public function notify(string $message): bool
    {
        if (!$this->token || !$this->chatId) {
            Log::warning('Telegram notification skipped: bot token or chat ID not configured.');
            return false;
        }

        try {
            $response = Http::timeout(5)->post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id'    => $this->chatId,
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram notification failed.', ['response' => $response->body()]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Telegram notification exception: ' . $e->getMessage());
            return false;
        }
    }

    public function newRegistration(string $name, string $email): void
    {
        $this->notify(
            "🆕 <b>New Investor Registered</b>\n" .
            "Name: {$name}\n" .
            "Email: {$email}\n" .
            "Time: " . now()->format('Y-m-d H:i')
        );
    }

    public function newDeposit(string $investorName, float $amount, string $reference): void
    {
        $this->notify(
            "💰 <b>New Deposit Request</b>\n" .
            "Investor: {$investorName}\n" .
            "Amount: $" . number_format($amount, 2) . "\n" .
            "Reference: {$reference}\n" .
            "Time: " . now()->format('Y-m-d H:i')
        );
    }

    public function depositApproved(string $investorName, float $amount): void
    {
        $this->notify(
            "✅ <b>Deposit Approved</b>\n" .
            "Investor: {$investorName}\n" .
            "Amount: $" . number_format($amount, 2)
        );
    }

    public function newWithdrawal(string $investorName, float $amount, string $method): void
    {
        $this->notify(
            "🏦 <b>New Withdrawal Request</b>\n" .
            "Investor: {$investorName}\n" .
            "Amount: $" . number_format($amount, 2) . "\n" .
            "Method: {$method}\n" .
            "Time: " . now()->format('Y-m-d H:i')
        );
    }

    public function withdrawalApproved(string $investorName, float $amount): void
    {
        $this->notify(
            "✅ <b>Withdrawal Approved</b>\n" .
            "Investor: {$investorName}\n" .
            "Amount: $" . number_format($amount, 2)
        );
    }

    public function largeBalanceAdjustment(string $investorName, string $type, float $amount, string $reason): void
    {
        $this->notify(
            "⚠️ <b>Balance Adjustment</b>\n" .
            "Investor: {$investorName}\n" .
            "Type: " . ucfirst($type) . "\n" .
            "Amount: $" . number_format($amount, 2) . "\n" .
            "Reason: {$reason}"
        );
    }
}