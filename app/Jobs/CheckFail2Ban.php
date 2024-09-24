<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class CheckFail2Ban implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Command to check Fail2Ban status for sshd
        $output = shell_exec('fail2ban-client status sshd');
        $lines = explode("\n", $output);

        // Parse the output to find total failed attempts
        $totalFailed = 0;
        foreach ($lines as $line) {
            if (strpos($line, 'Total failed:') !== false) {
                // Extract the number of total failed attempts
                preg_match('/Total failed:\s+(\d+)/', $line, $matches);
                $totalFailed = isset($matches[1]) ? (int)$matches[1] : 0;
                break;
            }
        }

        // Check if total failed attempts are 3 or more
        if ($totalFailed >= 3) {
            // Send notification to Discord
            $this->sendDiscordNotification($totalFailed);
        }
    }

    protected function sendDiscordNotification($totalFailed)
    {
        $webhookUrl = env('DISCORD_WEBHOOK_URL');

        if ($webhookUrl) {
            $message = "⚠️ Alert: There have been {$totalFailed} failed login attempts on the server.";
            
            // Send a POST request to the Discord webhook
            Http::post($webhookUrl, [
                'content' => $message,
            ]);
        }
    }
}
