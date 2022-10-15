<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["candidat_serialize", "document_serialize"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["candidat_serialize", "document_serialize"])]
    private ?string $url = null;

    #[ORM\Column(length: 255)]
    #[Groups(["candidat_serialize", "document_serialize"])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(["candidat_serialize", "document_serialize"])]
    private ?string $extension = null;

    #[ORM\Column]
    #[Groups(["candidat_serialize", "document_serialize"])]
    private ?int $taille = null;

    #[ORM\ManyToOne(inversedBy: 'documents', cascade:["merge"])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidat $candidat = null;

    #[ORM\ManyToMany(targetEntity: TypeDocument::class, inversedBy: 'documents')]
    #[Groups(["document_serialize"])]
    private Collection $type;

    public function __construct()
    {
        $this->type = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
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

    public function getExtension(): ?string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): self
    {
        $this->extension = $extension;

        return $this;
    }

    public function getTaille(): ?int
    {
        return $this->taille;
    }

    public function setTaille(int $taille): self
    {
        $this->taille = $taille;

        return $this;
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

    /**
     * @return Collection<int, TypeDocument>
     */
    public function getType(): Collection
    {
        return $this->type;
    }

    public function addType(TypeDocument $type): self
    {
        if (!$this->type->contains($type)) {
            $this->type->add($type);
        }

        return $this;
    }

    public function removeType(TypeDocument $type): self
    {
        $this->type->removeElement($type);

        return $this;
    }
}
