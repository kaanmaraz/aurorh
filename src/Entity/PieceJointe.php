<?php

namespace App\Entity;

use App\Repository\PieceJointeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PieceJointeRepository::class)]
class PieceJointe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['pj_api'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pj_api'])]
    private ?string $chemin = null;

    #[ORM\Column(length: 255, nullable: true)] 
    #[Groups(['pj_api'])]
    private ?string $url = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['pj_api'])]
    private ?bool $actif = null;

    #[ORM\ManyToOne(inversedBy: 'pieceJointes')]
    private ?MailTemplate $mailTemplate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['pj_api'])]
    private ?string $nom = null;

    #[ORM\ManyToMany(targetEntity: MailEnvoye::class, mappedBy: 'pieceJointesMailEnvoye')]
    private Collection $mailEnvoyesCandidat;

    public function __construct()
    {
        $this->mailEnvoyesCandidat = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChemin(): ?string
    {
        return $this->chemin;
    }

    public function setChemin(?string $chemin): self
    {
        $this->chemin = $chemin;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    public function getMailTemplate(): ?MailTemplate
    {
        return $this->mailTemplate;
    }

    public function setMailTemplate(?MailTemplate $mailTemplate): self
    {
        $this->mailTemplate = $mailTemplate;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, MailEnvoye>
     */
    public function getMailEnvoyesCandidat(): Collection
    {
        return $this->mailEnvoyesCandidat;
    }

    public function addMailEnvoyesCandidat(MailEnvoye $mailEnvoyesCandidat): self
    {
        if (!$this->mailEnvoyesCandidat->contains($mailEnvoyesCandidat)) {
            $this->mailEnvoyesCandidat->add($mailEnvoyesCandidat);
            $mailEnvoyesCandidat->addPieceJointesMailEnvoye($this);
        }

        return $this;
    }

    public function removeMailEnvoyesCandidat(MailEnvoye $mailEnvoyesCandidat): self
    {
        if ($this->mailEnvoyesCandidat->removeElement($mailEnvoyesCandidat)) {
            $mailEnvoyesCandidat->removePieceJointesMailEnvoye($this);
        }

        return $this;
    }
}
