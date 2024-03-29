<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[Groups(['delivery-list', 'delivery-dash', 'activity-list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[Groups(['delivery-list', 'delivery-dash'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?DateTimeImmutable $harvestDate = null;
    
    #[Groups(['delivery-list', 'delivery-dash'])]
    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    private ?DateTimeImmutable $deliveryDate = null;
    
    #[Groups(['delivery-list'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Assert\Length(max:255)]
    private ?string $notes = null;

    #[Groups(['delivery-dash'])]
    private ?bool $lastWarning = null;

    #[Groups(['delivery-dash'])]
    private ?bool $warning = null;

    #[Groups(['delivery-list', 'delivery-dash'])]
    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $customer = null;
    
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: Activity::class, orphanRemoval: true)]
    private Collection $activities;

    #[Groups(['delivery-list', 'delivery-dash'])]
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: DeliveryProduct::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $deliveryProducts;
    
    #[Groups(['delivery-list'])]
    #[Assert\Length(max:10)]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $paymentMethod = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $deletedAt = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $deletedReason = null;
    
    public function __construct()
    {
        $this->activities = new ArrayCollection();
        $this->deliveryProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?User
    {
        return $this->customer;
    }

    public function setCustomer(?User $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function isWarning(): ?bool
    {
        return $this->warning;
    }

    public function setWarning(bool $warning): self
    {
        $this->warning = $warning;

        return $this;
    }

    public function isLastWarning(): ?bool
    {
        return $this->lastWarning;
    }

    public function setLastWarning(bool $lastWarning): self
    {
        $this->lastWarning = $lastWarning;

        return $this;
    }

    public function getHarvestDate(): DateTimeImmutable
    {
        return $this->harvestDate;
    }

    public function setHarvestDate(DateTimeImmutable $harvestDate): self
    {
        $this->harvestDate = $harvestDate;

        return $this;
    }

    public function getDeliveryDate(): DateTimeImmutable
    {
        return $this->deliveryDate;
    }

    public function setDeliveryDate(DateTimeImmutable $deliveryDate): self
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * @return Collection<int, Activity>
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities->add($activity);
            $activity->setDelivery($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->removeElement($activity)) {
            // set the owning side to null (unless already changed)
            if ($activity->getDelivery() === $this) {
                $activity->setDelivery(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DeliveryProduct>
     */
    public function getDeliveryProducts(): Collection
    {
        return $this->deliveryProducts;
    }

    /**
     * @param array<DeliveryProduct> $deliveryProducts
     */
    public function setDeliveryProducts(array $deliveryProducts): self
    {
        $this->deliveryProducts = new ArrayCollection($deliveryProducts);

        return $this;
    }

    public function addDeliveryProduct(DeliveryProduct $deliveryProduct): self
    {
        if (!$this->deliveryProducts->contains($deliveryProduct)) {
            $this->deliveryProducts->add($deliveryProduct);
            $deliveryProduct->setDelivery($this);
        }

        return $this;
    }

    public function removeDeliveryProduct(DeliveryProduct $deliveryProduct): self
    {
        if ($this->deliveryProducts->removeElement($deliveryProduct)) {
            // set the owning side to null (unless already changed)
            if ($deliveryProduct->getDelivery() === $this) {
                $deliveryProduct->setDelivery(null);
            }
        }

        return $this;
    }


    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTimeImmutable $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedReason(): ?string
    {
        return $this->deletedReason;
    }

    public function setDeletedReason(?string $deletedReason): self
    {
        $this->deletedReason = $deletedReason;

        return $this;
    }
}
