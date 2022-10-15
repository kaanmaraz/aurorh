<?php

namespace App\Entity;

use App\Repository\TypeCandidatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeCandidatRepository::class)]
class TypeCandidat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["candidat_serialize", "type_candidat_serialize"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["type_candidat_serialize"])]
    private ?string $libelle = null;

    #[ORM\OneToMany(mappedBy: 'typeCandidat', targetEntity: Candidat::class)]
    #[Groups(["type_candidat_serialize"])]
    private Collection $candidats;

    #[ORM\ManyToMany(targetEntity: TypeDocument::class, mappedBy: 'typeCandidats')]
    private Collection $documentsAFournir;

    public $tousTypeDocuments;

    public function __construct()
    {
        $this->candidats = new ArrayCollection();
        $this->documentsAFournir = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * @return Collection<int, Candidat>
     */
    public function getCandidats(): Collection
    {
        return $this->candidats;
    }

    public function addCandidat(Candidat $candidat): self
    {
        if (!$this->candidats->contains($candidat)) {
            $this->candidats->add($candidat);
            $candidat->setTypeCandidat($this);
        }

        return $this;
    }

    public function removeCandidat(Candidat $candidat): self
    {
        if ($this->candidats->removeElement($candidat)) {
            // set the owning side to null (unless already changed)
            if ($candidat->getTypeCandidat() === $this) {
                $candidat->setTypeCandidat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TypeDocument>
     */
    public function getDocumentsAFournir(): Collection
    {
        return $this->documentsAFournir;
    }

    public function addDocumentsAFournir(TypeDocument $documentsAFournir): self
    {
        if (!$this->documentsAFournir->contains($documentsAFournir)) {
            $this->documentsAFournir->add($documentsAFournir);
            $documentsAFournir->addTypeCandidat($this);
        }

        return $this;
    }

    public function removeDocumentsAFournir(TypeDocument $documentsAFournir): self
    {
        if ($this->documentsAFournir->removeElement($documentsAFournir)) {
            $documentsAFournir->removeTypeCandidat($this);
        }

        return $this;
    }

    public function getTousTypesDocuments(): ?array
    {
        return $this->tousTypeDocuments;
    }

    public function setTousTypesDocuments(?array $tousTypeDocuments)
    {
        $this->tousTypeDocuments = $tousTypeDocuments;

        return $this->tousTypeDocuments;
    }

    public function __toStringDocumentsPourMail()
    {
        $chaine = ""; 
        foreach ($this->documentsAFournir as $document) {
            $chaine .= "- " . $document . "\n"; 
        }
        return $chaine; 
    }
}
