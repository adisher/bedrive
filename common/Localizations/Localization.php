<?php namespace Common\Localizations;

use Common\Search\Searchable;
use Illuminate\Database\Eloquent\Model;

class Localization extends Model
{
    use Searchable;

    const MODEL_TYPE = 'localization';

    protected $guarded = ['id'];

    public function loadLines()
    {
        if (!$this->exists) {
            return;
        }

        $path = resource_path("lang/$this->language.json");

        if (file_exists($path)) {
            $this->lines = json_decode(file_get_contents($path), true);
        }
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'language' => $this->language,
            'created_at' => $this->created_at->timestamp ?? '_null',
            'updated_at' => $this->updated_at->timestamp ?? '_null',
        ];
    }

    public static function filterableFields(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    public function toNormalizedArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->language,
            'model_type' => self::MODEL_TYPE,
        ];
    }

    public static function getModelTypeAttribute(): string
    {
        return self::MODEL_TYPE;
    }
}
