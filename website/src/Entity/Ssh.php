<?php

namespace App\Entity;

use App\Repository\SshRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SshRepository::class)
 */
class Ssh
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $identifiant;

    /**
     * @ORM\Column(type="text")
     */
    private $motdepasse;

    /**
     * @ORM\ManyToOne(targetEntity=Server::class, inversedBy="sshes")
     */
    private $Server;



    public function __construct()
    {
        $this->servers = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifiant(): ?string
    {
        return $this->identifiant;
    }

    public function setIdentifiant(string $identifiant): self
    {
        $this->identifiant = $identifiant;

        return $this;
    }

    public function getMotdepasse(): ?string
    {
        return $this->motdepasse;
    }

    public function setMotdepasse(string $motdepasse): self
    {
        $this->motdepasse = $motdepasse;

        return $this;
    }

    public function getServer(): ?Server
    {
        return $this->Server;
    }

    public function setServer(?Server $Server): self
    {
        $this->Server = $Server;

        return $this;
    }



}
