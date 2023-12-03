<?php

namespace App\Validation;

use Symfony\Component\Validator\Constraint;

interface ConstraintInterface
{
    public function get(): Constraint;
}
