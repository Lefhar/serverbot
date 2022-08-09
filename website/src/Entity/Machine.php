<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MachineRepository::class)
 */
class Machine
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity=Server::class, mappedBy="machine")
     */
    private $Machine;

    public function __construct()
    {
        $this->Machine = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Server>
     */
    public function getMachine(): Collection
    {
        return $this->Machine;
    }

    public function addMachine(Server $machine): self
    {
        if (!$this->Machine->contains($machine)) {
            $this->Machine[] = $machine;
            $machine->setMachine($this);
        }

        return $this;
    }

    public function removeMachine(Server $machine): self
    {
        if ($this->Machine->removeElement($machine)) {
            // set the owning side to null (unless already changed)
            if ($machine->getMachine() === $this) {
                $machine->setMachine(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }
}
