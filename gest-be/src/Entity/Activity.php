<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity
{
    private const STATUS_PLANNED = 'planned';
    private const STATUS_WORKING = 'working';
    private const STATUS_DONE = 'done';
    private const STATUS_CANCELED = 'canceled';
    private const STATUS_ERROR = 'error';
    private const STATUSES = [
        self::STATUS_PLANNED,
        self::STATUS_WORKING,
        self::STATUS_DONE,
        self::STATUS_CANCELED,
        self::STATUS_ERROR,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Delivery $delivery = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 52)]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $week = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Step $step = null;

    #[ORM\Column(nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $workableFrom = null;

    #[ORM\Column(nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $workableUntil = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(self::STATUSES)]
    #[Assert\NotNull]
    private ?string $status = null;
    
    #[ORM\Column]
    #[Assert\Json]
    #[Assert\NotNull]
    private array $data = [];

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $executer = null;

    #[ORM\Column(nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $executionTime = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function setDelivery(?Delivery $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getWeek(): ?int
    {
        return $this->week;
    }

    public function setWeek(int $week): self
    {
        $this->week = $week;

        return $this;
    }

    public function getStep(): ?Step
    {
        return $this->step;
    }

    public function setStep(?Step $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getWorkableFrom(): ?\DateTimeImmutable
    {
        return $this->workableFrom;
    }

    public function setWorkableFrom(?\DateTimeImmutable $workableFrom): self
    {
        $this->workableFrom = $workableFrom;

        return $this;
    }

    public function getWorkableUntil(): ?\DateTimeImmutable
    {
        return $this->workableUntil;
    }

    public function setWorkableUntil(?\DateTimeImmutable $workableUntil): self
    {
        $this->workableUntil = $workableUntil;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getExecuter(): ?User
    {
        return $this->executer;
    }

    public function setExecuter(?User $executer): self
    {
        $this->executer = $executer;

        return $this;
    }

    public function getExecutionTime(): ?\DateTimeImmutable
    {
        return $this->executionTime;
    }

    public function setExecutionTime(?\DateTimeImmutable $executionTime): self
    {
        $this->executionTime = $executionTime;

        return $this;
    }
}
