<?php

namespace App\Entity;

use App\Repository\RestartRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RestartRepository::class)
 */
class Restart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Server::class, inversedBy="restarts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ippower;

    /**
     * @ORM\Column(type="integer")
     */
    private $etat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIppower(): ?Server
    {
        return $this->ippower;
    }

    public function setIppower(?Server $ippower): self
    {
        $this->ippower = $ippower;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
