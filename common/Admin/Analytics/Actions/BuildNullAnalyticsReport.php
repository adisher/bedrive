<?php

namespace Common\Admin\Analytics\Actions;

class BuildNullAnalyticsReport implements BuildAnalyticsReport
{
    public function execute(array $params): array
    {
        $dateRange = $params['dateRange'];

        return [
            'pageViews' => [
                'granularity' => $dateRange->granularity,
                'datasets' => [
                    [
                        'label' => __('Current period'),
                        'data' => [],
                    ],
                    [
                        'label' => __('Previous period'),
                        'data' => [],
                    ],
                ],
            ],
            'browsers' => [
                'granularity' => $dateRange->granularity,
                'datasets' => [
                    [
                        'label' => __('Sessions'),
                        'data' => [],
                    ],
                ],
            ],
            'locations' => [
                'granularity' => $dateRange->granularity,
                'datasets' => [
                    [
                        'label' => __('Sessions'),
                        'data' => [],
                    ],
                ],
            ],
            'devices' => [
                'granularity' => $dateRange->granularity,
                'datasets' => [
                    [
                        'label' => __('Sessions'),
                        'data' => [],
                    ],
                ],
            ],
            'platforms' => [
                'granularity' => $dateRange->granularity,
                'datasets' => [
                    [
                        'label' => __('Sessions'),
                        'data' => [],
                    ],
                ],
            ],
        ];
    }
}
