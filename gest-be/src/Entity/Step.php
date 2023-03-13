<?php

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[UniqueEntity(['name', 'product'])]
#[ORM\UniqueConstraint('step_name_product', ['name', 'product_id'])]
#[ORM\Entity(repositoryClass: StepRepository::class)]
class Step
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Product $product = null;

    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
    #[Assert\NotNull]
    private ?string $name = null;
    
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $minutes = null;
    
    #[ORM\Column]
    #[Assert\Json]
    private array $params = [];

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

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

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
