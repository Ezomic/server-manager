<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServerWentOffline extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Server $server) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("Server offline: {$this->server->name}")
            ->line("{$this->server->name} ({$this->server->hostname}) has not reported a heartbeat for over 2 minutes.")
            ->action('View server', route('servers.show', $this->server));
    }
}
