<?php

namespace App\Entity;

use App\Repository\SuspensionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SuspensionRepository::class)]
class Suspension
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $start = null;

    #[ORM\Column(nullable: true)]
    #[Assert\DateTime]
    private ?\DateTimeImmutable $stop = null;

    #[ORM\ManyToOne(inversedBy: 'suspensions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?User $prisoner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStart(): ?\DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(?\DateTimeImmutable $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getStop(): ?\DateTimeImmutable
    {
        return $this->stop;
    }

    public function setStop(?\DateTimeImmutable $stop): self
    {
        $this->stop = $stop;

        return $this;
    }

    public function getPrisoner(): ?User
    {
        return $this->prisoner;
    }

    public function setPrisoner(?User $prisoner): self
    {
        $this->prisoner = $prisoner;

        return $this;
    }
}
