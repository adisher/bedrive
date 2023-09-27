<?php

namespace Common\Admin\Analytics;

use Common\Admin\Analytics\Actions\BuildAnalyticsReport;
use Common\Admin\Analytics\Actions\BuildDemoAnalyticsReport;
use Common\Admin\Analytics\Actions\BuildGoogleAnalyticsReport;
use Common\Admin\Analytics\Actions\BuildNullAnalyticsReport;
use Illuminate\Support\ServiceProvider;

class AnalyticsServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(BuildAnalyticsReport::class, function () {
            if (config('common.site.demo')) {
                return new BuildDemoAnalyticsReport();
            } else {
                return $this->getGoogleAnalyticsData();
            }
        });
    }

    private function getGoogleAnalyticsData()
    {
        try {
            return new BuildGoogleAnalyticsReport();
        } catch (\Exception $e) {
            return new BuildNullAnalyticsReport();
        }
    }
}
