<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AbTestResultRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveTests(): Collection;
}
