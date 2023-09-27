<?php

namespace Common\Admin\Analytics\Actions;

interface GetAnalyticsHeaderDataAction
{
    /**
     * Get analytics header data.
     */
    public function execute(array $params): array;
}
