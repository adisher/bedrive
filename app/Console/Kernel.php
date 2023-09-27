<?php

namespace App\Console;

use App\Console\Commands\CleanDemoSite;
use App\Console\Commands\CreateDemoAccounts;
use App\Console\Commands\DeleteExpiredLinks;
use App\Console\Commands\EmptyTempDirectory;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DeleteExpiredLinks::class,
        EmptyTempDirectory::class,
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command(DeleteExpiredLinks::class)->everyMinute();

        $schedule->command('tempDir:empty')->hourly();

        if (config('common.site.demo')) {
            $schedule->command('demoSite:clean')->daily();
        }
    }

    protected function commands()
    {
        if (config('common.site.demo')) {
            $this->registerCommand(app(CreateDemoAccounts::class));
            $this->registerCommand(app(CleanDemoSite::class));
        }

        require base_path('routes/console.php');
    }
}
