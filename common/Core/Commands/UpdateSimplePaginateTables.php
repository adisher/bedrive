<?php

namespace Common\Core\Commands;

use Common\Settings\Settings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSimplePaginateTables extends Command
{
    protected $signature = 'simplePaginateTables:update';

    public function handle(): int
    {
        $max = 100000;

        $tables = [];

        collect(DB::select('SHOW TABLES'))
            ->map(function ($val) {
                foreach ($val as $key => $tbl) {
                    return $tbl;
                }
            })
            ->each(function ($table) use ($max, &$tables) {
                if (DB::table($table)->count() > $max) {
                    $tables[] = $table;
                }
            });

        app(Settings::class)->save([
            'simple_pagination_tables' => implode(',', $tables),
        ]);

        return Command::SUCCESS;
    }
}
