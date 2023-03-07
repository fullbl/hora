<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(['name', 'type'])]
#[ORM\UniqueConstraint('product_name_type', ['name', 'type'])]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(max: 100)]
    #[Assert\NotNull]
    private ?string $name = null;
    
    #[ORM\Column(length: 10)]
    #[Assert\Length(max: 10)]
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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
}
