<?php

namespace App\Entity;

use App\Repository\ZoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ZoneRepository::class)]
class Zone
{
    #[Groups(['zone-list', 'product-list', 'user-list', 'delivery-list', 'delivery-dash'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['zone-list', 'product-list', 'user-list', 'delivery-list', 'delivery-dash'])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[Groups(['zone-list', 'product-list', 'user-list', 'delivery-list', 'delivery-dash'])]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subzones')]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $subzones;

    public function __construct()
    {
        $this->subzones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubzones(): Collection
    {
        return $this->subzones;
    }

    public function addSubzone(self $subzone): static
    {
        if (!$this->subzones->contains($subzone)) {
            $this->subzones->add($subzone);
            $subzone->setParent($this);
        }

        return $this;
    }

    public function removeSubzone(self $subzone): static
    {
        if ($this->subzones->removeElement($subzone)) {
            // set the owning side to null (unless already changed)
            if ($subzone->getParent() === $this) {
                $subzone->setParent(null);
            }
        }

        return $this;
    }
}
