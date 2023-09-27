<?php namespace Common\Admin\Analytics;

use Carbon\CarbonImmutable;
use Common\Admin\Analytics\Actions\BuildAnalyticsReport;
use Common\Admin\Analytics\Actions\BuildNullAnalyticsReport;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\BaseController;
use Common\Database\Metrics\MetricDateRange;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected BuildAnalyticsReport $getDataAction,
        protected GetAnalyticsHeaderDataAction $getHeaderDataAction,
    ) {
    }

    public function report()
    {
        $this->authorize('index', 'ReportPolicy');

        $types = explode(',', $this->request->get('types', 'visitors,header'));
        $dateRange = $this->getDateRange();
        $cacheKey = sprintf(
            '%s-%s',
            $dateRange->getCacheKey(),
            implode(',', $types),
        );

        $response = [];
        $reportParams = ['dateRange' => $dateRange];
        if (in_array('visitors', $types)) {
            try {
                $response['visitorsReport'] = Cache::remember(
                    "adminReport.main.$cacheKey",
                    CarbonImmutable::now()->addDay(),
                    fn() => $this->getDataAction->execute($reportParams),
                );
            } catch (Exception $e) {
                $response['visitorsReport'] = app(
                    BuildNullAnalyticsReport::class,
                )->execute($reportParams);
            }
        }
        if (in_array('header', $types)) {
            $response['headerReport'] = Cache::remember(
                "adminReport.header.$cacheKey",
                CarbonImmutable::now()->addDay(),
                fn() => $this->getHeaderDataAction->execute($reportParams),
            );
        }

        return $this->success($response);
    }

    protected function getDateRange(): MetricDateRange
    {
        $startDate = $this->request->get('startDate');
        $endDate = $this->request->get('endDate');
        $timezone = $this->request->get('timezone', config('app.timezone'));

        return new MetricDateRange(
            start: $startDate,
            end: $endDate,
            timezone: $timezone,
        );
    }
}
