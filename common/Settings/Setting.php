<?php namespace Common\Settings;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['name', 'value'];

    protected $casts = ['private' => 'bool'];

    public function getValueAttribute(mixed $value): mixed
    {
        if ($value === 'false') {
            return false;
        }

        if ($value === 'true') {
            return true;
        }

        if (ctype_digit($value)) {
            return (int) $value;
        }

        return $value;
    }

    public function setValueAttribute($value)
    {
        if ($value === true) {
            $value = 'true';
        } elseif ($value === false) {
            $value = 'false';
        }

        $this->attributes['value'] = (string) $value;
    }
}
