<?php

namespace App\Abstracts;

use App\Traits\HasStandardValidationRules;
use App\Traits\HasUniqueRules;
use App\Traits\HasUpdateRules;

trait BaseValidationRules
{
    use HasUpdateRules;
    use HasUniqueRules;
    use HasStandardValidationRules;
}
