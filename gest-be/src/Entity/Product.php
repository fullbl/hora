<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
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
    public const TYPE_EXTRA = 'extra';
    public const TYPES = [
        self::TYPE_GROUND,
        self::TYPE_SEED,
        self::TYPE_SEEDS_BOX,
        self::TYPE_WATER_BOX,
        self::TYPE_BLACKOUT_BOX,
        self::TYPE_LIGHT_BOX,
        self::TYPE_SHIPPING_BOX,
        self::TYPE_EXTRA,
    ];

    #[Groups(['activity-list', 'delivery-list', 'product', 'product-edit', 'delivery-dash'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['delivery-list', 'product', 'product-edit', 'delivery-dash'])]
    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotNull]
    private ?string $name = null;
    
    #[Groups(['product', 'product-edit', 'delivery-dash'])]
    #[ORM\Column(length: 15)]
    #[Assert\Choice(self::TYPES)]
    #[Assert\NotNull]
    private ?string $type = null;
    
    #[Groups(['product', 'product-edit', 'delivery-dash'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull(groups: ['seeds'])]
    private ?int $decigrams = null;
    
    #[Groups(['product', 'product-edit'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull(groups: ['seeds'])]
    private ?int $days = null;
    
    #[Groups(['delivery-list', 'product', 'product-edit'])]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[ORM\Column(nullable: true)]
    private ?int $price = null;
    
    #[Groups(['product', 'delivery-dash'])]
    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Step::class, orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    private Collection $steps;
    
    #[Groups(['delivery-list', 'product', 'product-edit', 'delivery-dash'])]
    #[ORM\Column(options: ['default' => false])]
    private bool $weight = false;


    public function __construct()
    {
        $this->steps = new ArrayCollection();
    }

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

    public function getDecigrams(): ?int
    {
        return $this->decigrams;
    }

    public function setDecigrams(int $decigrams): self
    {
        $this->decigrams = $decigrams;

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

    public function getDays(): ?int
    {
        return $this->days;
    }

    public function setDays(int $days): self
    {
        $this->days = $days;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return Collection<int, Step>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param array<Step> $steps
     */
    public function setSteps(array $steps): self
    {
        $this->steps = new ArrayCollection($steps);
        
        return $this;
    }
    
    public function addStep(Step $step): self
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setproduct($this);
        }

        return $this;
    }

    public function removeStep(Step $step): self
    {
        if ($this->steps->removeElement($step)) {
            if ($step->getproduct() === $this) {
                $step->setproduct(null);
            }
        }

        return $this;
    }

    public function getWeight(): bool
    {
        return $this->weight;
    }

    public function setWeight(bool $weight): static
    {
        $this->weight = $weight;

        return $this;
    }
}
