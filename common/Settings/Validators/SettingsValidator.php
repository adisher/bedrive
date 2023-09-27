<?php

namespace Common\Settings\Validators;

interface SettingsValidator
{
    /**
     * @param array $values
     * @return null|array
     */
    public function fails($values);
}
