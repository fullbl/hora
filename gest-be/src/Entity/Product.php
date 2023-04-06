<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    public const TYPE_GROUND = 'ground';
    public const TYPE_SEED = 'seeds';
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

    #[Groups(['delivery-list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotNull]
    private ?string $name = null;
    
    #[Groups(['delivery-list'])]
    #[ORM\Column(length: 15)]
    #[Assert\Choice(self::TYPES)]
    #[Assert\NotNull]
    private ?string $type = null;
    
    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $grams = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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
