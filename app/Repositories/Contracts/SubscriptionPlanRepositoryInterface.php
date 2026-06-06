<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface SubscriptionPlanRepositoryInterface extends BaseRepositoryInterface
{
    public function getActivePlans(): Collection;
}
