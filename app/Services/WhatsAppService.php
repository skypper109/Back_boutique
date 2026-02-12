<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message (currently just logs)
     * 
     * @param string $phone
     * @param string $message
     * @return bool
     */
    public function sendMessage(string $phone, string $message): bool
    {
        // Normalize phone number (remove +, spaces, etc.)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // Evolution API requires phone with country code (e.g. 225...)
        // We assume the user has entered it correctly or we could add a prefix if missing
        
        $url = config('services.whatsapp.url');
        $key = config('services.whatsapp.key');
        $instance = config('services.whatsapp.instance');

        if (!$key || !$url || !$instance) {
            Log::error("WhatsApp configuration missing");
            return false;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'apikey' => $key
            ])->post("{$url}/message/sendText/{$instance}", [
                'number' => $cleanPhone,
                'text' => $message,
                'linkPreview' => true
            ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent to {$cleanPhone}");
                return true;
            } else {
                Log::error("WhatsApp API error: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("WhatsApp Service Exception: " . $e->getMessage());
            return false;
        }
    }
}
