<?php

namespace App\Mapper;

use App\Entity\Step;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class StepMapper
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function fromRequest(Request $request): Step
    {
        return $this->fill(new Step(), $request);
    }

    public function fill(Step $step, Request $request): Step
    {
        return $this->serializer->deserialize(
            $request->getContent(), 
            Step::class, 
            'json', 
            [AbstractNormalizer::OBJECT_TO_POPULATE => $step]
        );
    }
}
