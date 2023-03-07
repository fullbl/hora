<?php

namespace App\Entity;

use App\Repository\HorderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: HorderRepository::class)]
class Horder
{
    private const STATUS_DRAFT = 'draft';
    private const STATUS_ORDERED = 'ordered';
    private const STATUS_ARRIVED = 'arrived';
    private const STATUS_STORED = 'stored';
    private const STATUS_CANCELED = '”canceled”';
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
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'horder', targetEntity: OrderRow::class, orphanRemoval: true)]
    private Collection $orderRows;

    public function __construct()
    {
        $this->orderRows = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, OrderRow>
     */
    public function getOrderRows(): Collection
    {
        return $this->orderRows;
    }

    public function addOrderRow(OrderRow $orderRow): self
    {
        if (!$this->orderRows->contains($orderRow)) {
            $this->orderRows->add($orderRow);
            $orderRow->setHorder($this);
        }

        return $this;
    }

    public function removeOrderRow(OrderRow $orderRow): self
    {
        if ($this->orderRows->removeElement($orderRow)) {
            // set the owning side to null (unless already changed)
            if ($orderRow->getHorder() === $this) {
                $orderRow->setHorder(null);
            }
        }

        return $this;
    }
}
