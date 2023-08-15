<?php

namespace App\Mapper;

use App\Entity\Activity;
use App\Entity\Delivery;
use App\Entity\Step;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ActivityMapper
{
    public function __construct(
        private SerializerInterface $serializer,
        private Security $security,
        private EntityManagerInterface $em
    ) {
    }

    public function fromArray(array $data): Activity
    {
        $activity = new Activity();
        $activity
        ->setExecuter($this->security->getUser())
        ->setExecutionTime(new \DateTimeImmutable)
        ->setWorkableUntil(new \DateTimeImmutable($data['time']))
        ->setDelivery($this->em->getReference(Delivery::class, $data['delivery']))
        ->setStep($this->em->getReference(Step::class, $data['step']))
        ->setYear(($data['year']))
        ->setWeek(($data['week']))
        ->setQty(($data['qty']))
        ->setStatus(Activity::STATUS_PLANNED)
        ;
        return $activity;
    }

    public function fromRequest(Request $request): Activity
    {
        $activity = new Activity();
        $activity->setExecuter($this->security->getUser());
        $activity->setExecutionTime(new \DateTimeImmutable);

        return $this->fill($activity, $request);
    }

    public function fill(Activity $activity, Request $request): Activity
    {
        $activity = $this->serializer->deserialize(
            $request->getContent(),
            Activity::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $activity]
        );
        $data = $request->toArray();

        $activity->setDelivery($this->em->getReference(Delivery::class, $data['delivery']['id']));
        $activity->setStep($this->em->getReference(Step::class, $data['step']['id']));

        return $activity;
    }
}
