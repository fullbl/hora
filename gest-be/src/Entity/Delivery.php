<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(['weekDay', 'customer'])]
#[ORM\UniqueConstraint('weekday_customer', ['weekDay', 'customer_id'])]
#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[Groups(['delivery-list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[Groups(['delivery-list'])]
    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\Range(min: 1, max: 7)]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $weekDay = null;
    
    #[Groups(['delivery-list'])]
    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $customer = null;
    
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: Activity::class, orphanRemoval: true)]
    private Collection $activities;
    
    #[Groups(['delivery-list'])]
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: DeliveryProduct::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $deliveryProducts;

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

    public function getWeekDay(): int
    {
        return $this->weekDay;
    }

    public function setWeekDay(int $weekDay): self
    {
        $this->weekDay = $weekDay;

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
}
