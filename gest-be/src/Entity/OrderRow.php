<?php

namespace App\Entity;

use App\Repository\OrderRowRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRowRepository::class)]
class OrderRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderRows')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Horder $horder = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Product $product = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Positive]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHorder(): ?Horder
    {
        return $this->horder;
    }

    public function setHorder(?Horder $horder): self
    {
        $this->horder = $horder;

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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}
