<?php

namespace Common\Admin\Analytics\Actions;

interface BuildAnalyticsReport
{
    /**
     * Get data for admin area analytics page from active provider.
     * (Demo or Google Analytics currently)
     */
    public function execute(array $params): array;
}
