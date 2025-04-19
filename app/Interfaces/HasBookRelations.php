<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasBookRelations
{
  public function authors(): BelongsToMany;

  public function publisher(): BelongsTo;

  public function genres(): BelongsToMany;

  public function discounts(): BelongsToMany;

  public function suppliers(): BelongsToMany;
}
