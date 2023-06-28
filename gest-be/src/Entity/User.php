<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(['username'])]
#[ORM\UniqueConstraint('user_username', ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const STATUS_ACTIVE = 'active';
    private const STATUS_INACTIVE = 'inactive';
    private const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE
    ];

    #[Groups(['delivery-list'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Length(max: 180)]
    #[Assert\NotNull]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    #[Ignore]
    #[ORM\Column]
    #[Assert\NotNull]
    private ?string $password = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(self::STATUSES)]
    #[Assert\NotNull]
    private ?string $status = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotNull]
    private ?string $fullName = null;

    #[ORM\Column(length: 15)]
    #[Assert\Length(max: 15)]
    private ?string $vatNumber = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    private ?string $address = null;

    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Delivery::class)]
    private Collection $deliveries;

    #[ORM\OneToMany(mappedBy: 'prisoner', targetEntity: Suspension::class, orphanRemoval: true)]
    private Collection $suspensions;

    #[ORM\Column(length: 7, nullable: true)]
    #[Assert\Length(max: 7)]
    private ?string $sdi = null;

    #[Groups(['delivery-list'])]
    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $zone = null;
    
    #[Groups(['delivery-list'])]
    #[ORM\Column(options: ['default' => 0])]
    #[Assert\Type('integer')]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\NotNull]
    private int $discount = 0;

    public function __construct()
    {
        $this->deliveries = new ArrayCollection();
        $this->suspensions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getVatNumber(): ?string
    {
        return $this->vatNumber;
    }

    public function setVatNumber(string $vatNumber): self
    {
        $this->vatNumber = $vatNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Delivery>
     */
    public function getDeliveries(): Collection
    {
        return $this->deliveries;
    }

    public function addDelivery(Delivery $delivery): self
    {
        if (!$this->deliveries->contains($delivery)) {
            $this->deliveries->add($delivery);
            $delivery->setCustomer($this);
        }

        return $this;
    }

    public function removeDelivery(Delivery $delivery): self
    {
        if ($this->deliveries->removeElement($delivery)) {
            // set the owning side to null (unless already changed)
            if ($delivery->getCustomer() === $this) {
                $delivery->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Suspension>
     */
    public function getSuspensions(): Collection
    {
        return $this->suspensions;
    }

    public function addSuspension(Suspension $suspension): self
    {
        if (!$this->suspensions->contains($suspension)) {
            $this->suspensions->add($suspension);
            $suspension->setPrisoner($this);
        }

        return $this;
    }

    public function removeSuspension(Suspension $suspension): self
    {
        if ($this->suspensions->removeElement($suspension)) {
            // set the owning side to null (unless already changed)
            if ($suspension->getPrisoner() === $this) {
                $suspension->setPrisoner(null);
            }
        }

        return $this;
    }

    public function getSdi(): ?string
    {
        return $this->sdi;
    }

    public function setSdi(?string $sdi): self
    {
        $this->sdi = $sdi;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function setDiscount(int $discount): self
    {
        $this->discount = $discount;

        return $this;
    }
}
