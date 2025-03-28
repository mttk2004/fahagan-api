<?php

namespace App\Interfaces;

use Illuminate\Support\Collection;

interface Discountable
{
    public function getAllActiveDiscounts(): Collection;

    public function getBestDiscount(): ?object;

    public function getActiveDiscounts($query, $now): Collection;
}
