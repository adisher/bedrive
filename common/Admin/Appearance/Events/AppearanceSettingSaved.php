<?php

namespace Common\Admin\Appearance\Events;

class AppearanceSettingSaved
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $previousValue;

    public function __construct(
        string $type,
        string $key,
        string $value,
        string $previousValue = null
    ) {
        //
        $this->type = $type;
        $this->key = $key;
        $this->value = $value;
        $this->previousValue = $previousValue;
    }
}
