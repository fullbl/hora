<?php

namespace App\Entity;

use App\Repository\HorderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HorderRepository::class)]
class Horder
{
    public const STATUS_DRAFT = 'draft';
    private const STATUS_ORDERED = 'ordered';
    private const STATUS_ARRIVED = 'arrived';
    private const STATUS_STORED = 'stored';
    private const STATUS_CANCELED = 'canceled';
    private const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ORDERED,
        self::STATUS_ARRIVED,
        self::STATUS_STORED,
        self::STATUS_CANCELED
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(self::STATUSES)]
    #[Assert\NotNull]
    private string $status = self::STATUS_DRAFT;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Product $product = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $decigrams = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getDecigrams(): ?int
    {
        return $this->decigrams;
    }

    public function setDecigrams(int $decigrams): self
    {
        $this->decigrams = $decigrams;

        return $this;
    }
}
