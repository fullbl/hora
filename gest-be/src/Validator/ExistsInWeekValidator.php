<?php

namespace App\Validator;

use App\Entity\Delivery;
use App\Repository\DeliveryRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use UnexpectedValueException;

class ExistsInWeekValidator extends ConstraintValidator
{
    public function __construct(private DeliveryRepository $repo)
    {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$value instanceof Delivery) {
            throw new UnexpectedValueException($value, Delivery::class);
        }
        $weeks = $this->repo->intersectingWeeks($value->getWeeks(), $value->getCustomer(), $value->getId());
        if (!empty($weeks)) {
            $this->context
                ->buildViolation(sprintf($constraint->message, implode(', ', $weeks)))
                ->atPath('customer')
                ->addViolation();
        }
    }
}
