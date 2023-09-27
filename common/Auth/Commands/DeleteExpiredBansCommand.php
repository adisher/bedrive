<?php

namespace Common\Auth\Commands;

use Common\Auth\Ban;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DeleteExpiredBansCommand extends Command
{
    protected $signature = 'bans:deleteExpired';

    public function handle(): int
    {
        $bans = Ban::query()
            ->where('expired_at', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->get();

        $bans->each(function ($ban) {
            $ban->created_by->fill(['banned_at' => null])->save();
            $ban->delete();
        });

        return Command::SUCCESS;
    }
}
