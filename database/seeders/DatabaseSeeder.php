<?php

namespace Database\Seeders;

use Common\Tags\Tag;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function __construct(private Tag $tag)
    {
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create tag for starring file entries
        $this->tag->firstOrCreate([
            'name' => 'starred',
            'display_name' => 'Starred',
            'type' => 'label',
        ]);

        $this->call(WorkspaceRoleSeeder::class);

        $this->call(SeedDemoProducts::class);
    }
}
