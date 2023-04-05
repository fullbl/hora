<?php

namespace App\Entity;

use App\Repository\StorageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity(['type'])]
#[ORM\UniqueConstraint('storage_type', ['type'])]
#[ORM\Entity(repositoryClass: StorageRepository::class)]
class Storage
{
    public const TYPE_GROUND = 'ground';
    public const TYPE_SEED = 'seed';
    public const TYPE_SEEDS_BOX = 'seeds_box';
    public const TYPE_WATER_BOX = 'water_box';
    public const TYPE_BLACKOUT_BOX = 'blackout_box';
    public const TYPE_LIGHT_BOX = 'light_box';
    public const TYPE_SHIPPING_BOX = 'shipping_box';
    public const TYPES = [
        self::TYPE_GROUND,
        self::TYPE_SEED,
        self::TYPE_SEEDS_BOX,
        self::TYPE_WATER_BOX,
        self::TYPE_BLACKOUT_BOX,
        self::TYPE_LIGHT_BOX,
        self::TYPE_SHIPPING_BOX,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 15)]
    #[Assert\Choice(self::TYPES)]
    #[Assert\NotNull]
    private ?string $type = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $grams = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrams(): ?int
    {
        return $this->grams;
    }

    public function setGrams(int $grams): self
    {
        $this->grams = $grams;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
