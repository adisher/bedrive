<?php

namespace App\Providers;

use App\FileEntry;
use App\Services\Admin\GetAnalyticsHeaderData;
use App\Services\AppBootstrapData;
use App\Services\Entries\SetPermissionsOnEntry;
use Common\Admin\Analytics\Actions\GetAnalyticsHeaderDataAction;
use Common\Core\Bootstrap\BootstrapData;
use Common\Files\FileEntry as CommonFileEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

const WORKSPACED_RESOURCES = [FileEntry::class];
const WORKSPACE_HOME_ROUTE = '/drive';

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            GetAnalyticsHeaderDataAction::class,
            GetAnalyticsHeaderData::class,
        );

        $this->app->bind(BootstrapData::class, AppBootstrapData::class);

        $this->app->bind(CommonFileEntry::class, FileEntry::class);

        $this->app->singleton(
            SetPermissionsOnEntry::class,
            fn() => new SetPermissionsOnEntry(),
        );
    }

    public function boot()
    {
        Model::preventLazyLoading(!app()->isProduction());
    }
}
