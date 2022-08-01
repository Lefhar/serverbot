<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ServerRepository::class)
 */
class Server
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
     * @ORM\Column(type="integer")
     */
    private $ippower;

    /**
     * @ORM\Column(type="integer")
     */
    private $etat;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ipv4;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $localscript;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lieninfo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Users::class, inversedBy="servers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity=Ssh::class, mappedBy="Server")
     */
    private $sshes;

    public function __construct()
    {
        $this->sshes = new ArrayCollection();
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

    public function getIppower(): ?int
    {
        return $this->ippower;
    }

    public function setIppower(int $ippower): self
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

    public function getIpv4(): ?string
    {
        return $this->ipv4;
    }

    public function setIpv4(string $ipv4): self
    {
        $this->ipv4 = $ipv4;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLocalscript(): ?string
    {
        return $this->localscript;
    }

    public function setLocalscript(?string $localscript): self
    {
        $this->localscript = $localscript;

        return $this;
    }

    public function getLieninfo(): ?string
    {
        return $this->lieninfo;
    }

    public function setLieninfo(?string $lieninfo): self
    {
        $this->lieninfo = $lieninfo;

        return $this;
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

    public function getUsers(): ?Users
    {
        return $this->users;
    }

    public function setUsers(?Users $users): self
    {
        $this->users = $users;

        return $this;
    }

    /**
     * @return Collection<int, Ssh>
     */
    public function getSshes(): Collection
    {
        return $this->sshes;
    }

    public function addSsh(Ssh $ssh): self
    {
        if (!$this->sshes->contains($ssh)) {
            $this->sshes[] = $ssh;
            $ssh->setServer($this);
        }

        return $this;
    }

    public function removeSsh(Ssh $ssh): self
    {
        if ($this->sshes->removeElement($ssh)) {
            // set the owning side to null (unless already changed)
            if ($ssh->getServer() === $this) {
                $ssh->setServer(null);
            }
        }

        return $this;
    }


}
