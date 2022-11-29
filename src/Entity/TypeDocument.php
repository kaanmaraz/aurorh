<?php

namespace App\Entity;

use App\Repository\TypeDocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TypeDocumentRepository::class)]
#[ORM\HasLifecycleCallbacks] 
class TypeDocument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["type_candidat_serialize", "document_serialize", "type_document_serialize"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["type_candidat_serialize", "document_serialize", "type_document_serialize"])]
    private ?string $libelle = null;

    #[ORM\Column]
    #[Groups(["type_candidat_serialize", "document_serialize", "type_document_serialize"])]
    private ?bool $obligatoire = null;

    #[ORM\ManyToMany(targetEntity: TypeCandidat::class, inversedBy: 'documentsAFournir')]
    private Collection $typeCandidats;

    #[ORM\Column]
    #[Groups(["type_candidat_serialize", "document_serialize", "type_document_serialize"])]
    private ?bool $multiple = null;

    #[ORM\Column(length: 10)]
    #[Groups(["type_candidat_serialize", "document_serialize", "type_document_serialize"])]
    private ?string $format = null;

    #[ORM\ManyToMany(targetEntity: Document::class, mappedBy: 'type')]
    private Collection $documents;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    public function __construct()
    {
        $this->typeCandidats = new ArrayCollection();
        $this->documents = new ArrayCollection();
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

    public function isObligatoire(): ?bool
    {
        return $this->obligatoire;
    }

    public function setObligatoire(bool $obligatoire): self
    {
        $this->obligatoire = $obligatoire;

        return $this;
    }

    /**
     * @return Collection<int, TypeCandidat>
     */
    public function getTypeCandidats(): Collection
    {
        return $this->typeCandidats;
    }

    public function addTypeCandidat(TypeCandidat $typeCandidat): self
    {
        if (!$this->typeCandidats->contains($typeCandidat)) {
            $this->typeCandidats->add($typeCandidat);
        }

        return $this;
    }

    public function removeTypeCandidat(TypeCandidat $typeCandidat): self
    {
        $this->typeCandidats->removeElement($typeCandidat);

        return $this;
    }

    public function isMultiple(): ?bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->addType($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            $document->removeType($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle . " (" . $this->format . ")"; 
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSlug(): self
    {
        $slugger = new AsciiSlugger(); 
        $libelle = $slugger->slug($this->libelle, '_'); 
        $libelle = strtolower($libelle); 
        $this->slug = $libelle;

        return $this;
    }
}
