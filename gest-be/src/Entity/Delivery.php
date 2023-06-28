<?php

namespace App\Entity;

use App\Repository\DeliveryRepository;
use App\Validator\ExistsInWeek;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ExistsInWeek]
#[ORM\Entity(repositoryClass: DeliveryRepository::class)]
class Delivery
{
    #[Groups(['delivery-list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(type: Types::SMALLINT, name: 'week_day')]
    #[Assert\Range(min: 0, max: 6)]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $harvestWeekDay = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(type: Types::SMALLINT, options: ["default" => 1])]
    #[Assert\Range(min: 0, max: 6)]
    #[Assert\Type('integer')]
    #[Assert\NotNull]
    private ?int $deliveryWeekDay = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Assert\Length(max:255)]
    private ?string $notes = null;

    #[Groups(['delivery-list'])]
    #[Assert\NotNull]
    #[Assert\All([
        new Assert\Range(min: 1, max: 53),
    ])]
    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $weeks = [];

    #[Groups(['delivery-list'])]
    #[ORM\ManyToOne(inversedBy: 'deliveries')]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $customer = null;
    
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: Activity::class, orphanRemoval: true)]
    private Collection $activities;

    #[Groups(['delivery-list'])]
    #[ORM\OneToMany(mappedBy: 'delivery', targetEntity: DeliveryProduct::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $deliveryProducts;
    
    #[Groups(['delivery-list'])]
    #[Assert\Length(max:10)]
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $paymentMethod = null;
    
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

    public function getHarvestWeekDay(): int
    {
        return $this->harvestWeekDay;
    }

    public function setHarvestWeekDay(int $harvestWeekDay): self
    {
        $this->harvestWeekDay = $harvestWeekDay;

        return $this;
    }

    public function getDeliveryWeekDay(): int
    {
        return $this->deliveryWeekDay;
    }

    public function setDeliveryWeekDay(int $deliveryWeekDay): self
    {
        $this->deliveryWeekDay = $deliveryWeekDay;

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

    public function getWeeks(): array
    {
        return array_map(fn($w) => (int)$w, $this->weeks);
    }

    public function setWeeks(array $weeks): self
    {
        $this->weeks = $weeks;

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
}
