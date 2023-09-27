<?php

namespace Common\Auth\Jobs;

use Common\Auth\ActiveSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActiveSessionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $data)
    {
    }

    public function handle(): void
    {
        $sessionId = $this->data['session_id'] ?? null;
        $token = $this->data['token'] ?? null;

        $existingSession = app(ActiveSession::class)
            ->when(
                $sessionId,
                fn($query) => $query->where('session_id', $sessionId),
            )
            ->when($token, fn($query) => $query->where('token', $token))
            ->where('user_id', $this->data['user_id'])
            ->first();

        if ($existingSession) {
            $existingSession->touch('updated_at');
        } else {
            $this->createNewSession();
        }
    }

    protected function createNewSession()
    {
        ActiveSession::create([
            'session_id' => $this->data['session_id'] ?? null,
            'token' => $this->data['token'] ?? null,
            'user_id' => $this->data['user_id'],
            'ip_address' => $this->data['ip_address'] ?? null,
            'user_agent' => $this->data['user_agent'] ?? null,
        ]);
    }
}
