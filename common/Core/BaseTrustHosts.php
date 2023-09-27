<?php

namespace Common\Core;

use Common\Domains\CustomDomainController;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

class BaseTrustHosts extends Middleware
{
    public function hosts(): array
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }

    protected function shouldSpecifyTrustedHosts(): bool
    {
        // allow custom domain validation
        if (request()->path() === CustomDomainController::VALIDATE_CUSTOM_DOMAIN_PATH) {
            return false;
        } else {
            return parent::shouldSpecifyTrustedHosts();
        }
    }
}
