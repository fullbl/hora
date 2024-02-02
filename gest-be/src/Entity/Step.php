<?php

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity(['name', 'product'])]
#[ORM\UniqueConstraint('step_name_product', ['name', 'product_id'])]
#[ORM\Entity(repositoryClass: StepRepository::class)]
#[OrderBy('sort', 'asc')]
class Step
{
    public const STEP_SOAKING = 'soaking';
    public const STEP_HOT_SOAKING = 'hot_soaking';
    public const STEP_PREACTIVATION = 'preactivation';
    public const STEP_BLACKOUT = 'blackout';
    public const STEP_LIGHT = 'light';

    public const STEP_TYPES = [
        self::STEP_SOAKING,
        self::STEP_HOT_SOAKING,
        self::STEP_PREACTIVATION,
        self::STEP_BLACKOUT,
        self::STEP_LIGHT,
    ];

    #[Groups(['activity-list', 'product-list', 'delivery-dash'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
  
    #[Groups(['activity-list'])]
    #[ORM\ManyToOne(inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;
    
    #[Groups(['activity-list', 'product-list', 'delivery-dash'])]
    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
    #[Assert\NotNull]
    private ?string $name = null;
    
    #[Groups(['product-list', 'delivery-dash'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $minutes = null;
    
    #[Groups(['product-list'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $sort = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMinutes(): ?int
    {
        return $this->minutes;
    }

    public function setMinutes(int $minutes): self
    {
        $this->minutes = $minutes;

        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }
}
