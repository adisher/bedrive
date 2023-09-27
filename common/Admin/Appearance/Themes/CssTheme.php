<?php

namespace Common\Admin\Appearance\Themes;

use Illuminate\Database\Eloquent\Model;

class CssTheme extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'is_dark' => 'boolean',
        'default_dark' => 'boolean',
        'default_light' => 'boolean',
    ];

    public function setColorsAttribute($value)
    {
        if ($value && is_array($value)) {
            $this->attributes['colors'] = json_encode($value);
        }
    }

    public function getColorsAttribute($value): array
    {
        if ($value && is_string($value)) {
            return json_decode($value, true);
        } else {
            return [];
        }
    }

    public function getColorsForCss(): string
    {
        // don't decode from json
        $colors = $this->attributes['colors'];
        $colors = preg_replace('/"/', '', $colors);
        $colors = preg_replace('/[{}]/', '', $colors);
        return preg_replace('/, ?--/', ';--', $colors);
    }

    public function getHtmlThemeColor()
    {
        if ($this->is_dark) {
            return $this->colors['--be-background-alt'];
        } else {
            return $this->colors['--be-primary'];
        }
    }
}
