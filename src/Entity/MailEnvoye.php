<?php

namespace App\Entity;

use App\Repository\MailEnvoyeRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\MailTemplate;
use Scheb\TwoFactorBundle\Security\Http\EventListener\SuppressRememberMeListener;

#[ORM\Entity(repositoryClass: MailEnvoyeRepository::class)]
#[ORM\HasLifecycleCallbacks]
class MailEnvoye extends MailTemplate
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contenu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $sujet = null;

    #[ORM\ManyToOne(inversedBy: 'mailEnvoyes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidat $candidat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateEnvoi = null;

    #[ORM\ManyToMany(targetEntity: PieceJointe::class, inversedBy: 'mailEnvoyesCandidat')]
    private Collection $pieceJointesMailEnvoye;

    public function __construct()
    {
        parent::__construct();
        $this->pieceJointesMailEnvoye = new ArrayCollection(); 
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidat(): ?Candidat
    {
        return $this->candidat;
    }

    public function setCandidat(?Candidat $candidat): self
    {
        $this->candidat = $candidat;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    #[ORM\PrePersist]
    public function setDateEnvoi(): self
    {
        $this->dateEnvoi = new DateTime();

        return $this;
    }

    /**
     * @return Collection<int, PieceJointe>
     */
    public function getPieceJointesMailEnvoye(): Collection
    {
        return $this->pieceJointesMailEnvoye;
    }

    public function addPieceJointesMailEnvoye(PieceJointe $pieceJointesMailEnvoye): self
    {
        if (!$this->pieceJointesMailEnvoye->contains($pieceJointesMailEnvoye)) {
            $this->pieceJointesMailEnvoye->add($pieceJointesMailEnvoye);
        }

        return $this;
    }

    public function removePieceJointesMailEnvoye(PieceJointe $pieceJointesMailEnvoye): self
    {
        $this->pieceJointesMailEnvoye->removeElement($pieceJointesMailEnvoye);

        return $this;
    }

}
