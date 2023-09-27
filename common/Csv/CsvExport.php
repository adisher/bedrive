<?php

namespace Common\Csv;

use Illuminate\Database\Eloquent\Model;
use Storage;

class CsvExport extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
    ];

    public function storeFile($stream): bool
    {
        Storage::delete($this->filePath());
        return Storage::writeStream($this->filePath(), $stream);
    }

    public function filePath(): string
    {
        return "exports/csv/{$this->uuid}.csv";
    }

    public function downloadLink(): string
    {
        return url("csv/download/$this->id");
    }
}
