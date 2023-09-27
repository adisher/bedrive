<?php

namespace Common\Auth\Jobs;

use Common\Auth\Roles\Role;
use Common\Csv\BaseCsvExportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ExportRolesCsv extends BaseCsvExportJob
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    protected $requesterId;

    public function __construct(int $requesterId)
    {
        $this->requesterId = $requesterId;
    }

    public function cacheName(): string
    {
        return 'roles';
    }

    protected function generateLines()
    {
        $selectCols = [
            'id',
            'name',
            'description',
            'type',
            'internal',
            'created_at',
            'updated_at',
        ];

        Role::select($selectCols)->chunkById(100, function (Collection $chunk) {
            $chunk->each(function (Role $role) {
                $data = $role->toArray();
                $this->writeLineToCsv($data);
            });
        });
    }
}
