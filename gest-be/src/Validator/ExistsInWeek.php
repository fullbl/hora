<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ExistsInWeek extends Constraint
{
    public string $message = 'The delivery already exists for this customer in following weeks: %s.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}