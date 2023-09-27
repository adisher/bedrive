<?php

namespace Common\Admin\Analytics\Actions;

use Common\Database\Metrics\Trend;

class DemoTrend extends Trend
{
    protected function aggregate(string $function): array
    {
        $data = array_map(function($item) {
            $item['value'] = random_int(100, 500);
            return $item;
        }, $this->getAllPossibleDateResults($this->dateRange));
        
        return array_values($data);
    }
}
