<?php

namespace App\Entity;

use App\Repository\CandidatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CandidatRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'Un candidat avec ce mail exite déjà',
)]
class Candidat
{

    //Lorsqu'on génère un mot de passe pour le candidat c'est le nombre de caractère du mot de passe
    public const NB_CHAR_MDP = 12;

    public const STATUTS = [
        "attente_envoi_mail_form" => [
            "LIBELLE" => "En attente de l'envoi du formulaire"  ,
            "ACTION" => "Envoyer formulaire" , 
            "ROUTE" => "candidat_envoi_lien"
        ],
        "form_mail_envoye" => [
            "LIBELLE" =>  "Formulaire envoyé" ,
            "ACTION" => "aucune" 
        ],
        "attente_controle_form" => [
            "LIBELLE" =>  "En attente de contrôle des infos" ,
            "ACTION" => "Contrôler infos" , 
            "ROUTE" => "candidat_controle_infos"
        ],
        "form_valid" => [
            "LIBELLE" =>  "Informations contrôlées valides" ,
            "ACTION" => "aucune"
        ],
        "form_invalid" => [
            "LIBELLE" => "Informations contrôlées invalides" ,
            "ACTION" => "aucune"
        ],
        "complet" => [
            "LIBELLE" => "Récupération des informations terminée" ,
            "ACTION" => "aucune"
        ],
    ];

    //Les niveaux de salaire avec les coefficients liés
    public const NIVEAU_COEFF = [
        '2' => ['Coeff base' => 198, 'Coeff dvpe' => 206, 'pts de garantie' => 8],
        '3' => ['Coeff base' => 215, 'Coeff dvpe' => 221, 'pts de garantie' => 6],
        '4' => ['Coeff base' => 240, 'Coeff dvpe' => 244, 'pts de garantie' => 6],
        '5A' => ['Coeff base' => 260, 'Coeff dvpe' => 262, 'pts de garantie' => 6],
        '5B' => ['Coeff base' => 285, 'Coeff dvpe' => 0, 'pts de garantie' => 0],
        '6' => ['Coeff base' => 315, 'Coeff dvpe' => 0, 'pts de garantie' => 0],
        '7' => ['Coeff base' => 360, 'Coeff dvpe' => 0, 'pts de garantie' => 0],
        '8' => ['Coeff base' => 400, 'Coeff dvpe' => 0, 'pts de garantie' => 0],
        '9' => ['Coeff base' => 430, 'Coeff dvpe' => 0, 'pts de garantie' => 0],
    ];
    public $listeService; 

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["candidat_serialize","document_serialize","soumis_candidat_serialize","lien_serialize"])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotNull(
        message: "Ce champ est requis"
    )]
    #[Groups(["candidat_serialize","document_serialize","soumis_candidat_serialize"])]
    private ?string $nom = null;

    #[ORM\Column(length: 50)]
    #[Groups(["candidat_serialize","document_serialize","soumis_candidat_serialize"])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $adresse = null;

    #[ORM\Column(length: 5, nullable: true)]
     #[Assert\Regex(
            pattern: "~^[0-9]{5}$~",
            htmlPattern: "[0-9]{5}",
            message:"Veuillez entrer un code postal au bon format"
        )]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $codePostal = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $ville = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]

    private ?\DateTimeInterface $dateDeNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $villeNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $departementNaissance = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $paysNaissance = null;

    #[ORM\Column(length: 15, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    #[Assert\Length(min:13,max: 13)]
    private ?string $numeroSs = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $nomUsage = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateExpirationTs = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $sexe = null;

    #[ORM\Column(length: 255, unique:true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    #[Assert\Email()]
    #[Assert\NotNull(
        message: "Ce champ est requis"
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(
        value: "today", 
        message: "La date prévisionnelle d'embauche doit être supérieure ou égale à la date du jour"
    )]
    private ?\DateTimeInterface $datePrevisEmbauche = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(
        message: "Ce champ est requis"
    )]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $poste = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull(
        message: "Ce champ est requis"
    )]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $site = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(
        value: "today", 
        message: "La date d'éxpiration du formulaire doit être supérieure ou égale à la date du jour"
    )]
    #[Assert\LessThanOrEqual(
        propertyPath: "datePrevisEmbauche",
        message: "Le délai d'éxpiration du formulaire doit être inférieur à la date prévisionnelle d'embauche"
    )]
    private ?\DateTimeInterface $delaiFormulaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize","lien_serialize"])]
    private ?string $mdp = null;

    #[ORM\Column(length: 255, nullable: true, unique:true)]
     #[Assert\Regex(
           pattern: "~^[0-9]{5}$~",
            htmlPattern: "[0-9]{5}",
            message:"Veuillez entrer uniquement des chiffres"
        )]
    private ?string $numeroAgent = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(
        value: "today", 
        message: "La date de début de contrat doit être supérieure ou égale à la date du jour", 
    )]
    #[Assert\LessThanOrEqual(
        propertyPath: "finCDD", 
        message: "La date de début de contrat doit être inférieure ou égale à la date de fin de contrat"
    )]
    private ?\DateTimeInterface $debutCDD = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\GreaterThanOrEqual(
        value: "today", 
        message: "La date prévisionnelle de fin du contrat doit être supérieure ou égale à la date du jour"
    )]
    private ?\DateTimeInterface $finCDD = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $service = null;

    #[ORM\Column(nullable: true)]
    private ?int $coeffDeveloppe = null;

    #[ORM\Column(nullable: true)]
    private ?int $ptsGarantie = null;

    #[ORM\Column(length: 2, nullable: true)]
    private ?string $niveauSalaire = null;

    #[ORM\Column(nullable: true)]
    private ?int $coeffBase = null;

    #[ORM\Column(nullable: true)]
    private ?int $ptsCompetences = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $periodeEssai = null;

    #[ORM\Column(nullable: true)]
    private ?int $ptsExperience = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prime = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["candidat_serialize"])]
    private ?bool $aDiplome = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nationnalite = null;

    #[ORM\Column(nullable: true)]
    private ?int $typeNature = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $typeReferentiel = null;

    #[ORM\Column(nullable: true)]
    private ?bool $dejaComplete = null;

    #[ORM\Column(nullable: true)]
    private ?bool $supprime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateSuppression = null;

    #[ORM\Column(length: 5, nullable: true)]
    private ?string $numeroAgentManager = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(min: 2,max: 2)]
    private ?string $cle = null;

    #[ORM\OneToMany(mappedBy: 'candidat', targetEntity: Document::class, orphanRemoval: true)]
    #[Groups(["soumis_candidat_serialize"])]
    private Collection $documents;

    #[ORM\OneToOne(mappedBy: 'candidat', cascade: ['persist', 'remove'])]
    #[Groups(["candidat_serialize"])]
    private ?Lien $lien = null;

    #[ORM\ManyToOne(inversedBy: 'candidats')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(
        message: "Ce champ est requis"
    )]
    #[Groups(["candidat_serialize"])]

    private ?TypeCandidat $typeCandidat = null;

    #[ORM\Column(type: Types::JSON)]
    private array $statut = [];

    #[ORM\OneToMany(mappedBy: 'candidat', targetEntity: MailEnvoye::class, orphanRemoval: true)]
    private Collection $mailEnvoyes;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["candidat_serialize", "soumis_candidat_serialize"])]
    private ?string $complementAdresse = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->mailEnvoyes = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getCodePostal(): ?string
    {
        return $this->codePostal;
    }

    public function setCodePostal(?string $codePostal): self
    {
        $this->codePostal = $codePostal;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(?string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getDateDeNaissance(): ?\DateTimeInterface
    {
        return $this->dateDeNaissance;
    }

    public function setDateDeNaissance(?\DateTimeInterface $dateDeNaissance): self
    {
        $this->dateDeNaissance = $dateDeNaissance;

        return $this;
    }

    public function getVilleNaissance(): ?string
    {
        return $this->villeNaissance;
    }

    public function setVilleNaissance(?string $villeNaissance): self
    {
        $this->villeNaissance = $villeNaissance;

        return $this;
    }

    public function getDepartementNaissance(): ?string
    {
        return $this->departementNaissance;
    }

    public function setDepartementNaissance(?string $departementNaissance): self
    {
        $this->departementNaissance = $departementNaissance;

        return $this;
    }

    public function getPaysNaissance(): ?string
    {
        return $this->paysNaissance;
    }

    public function setPaysNaissance(?string $paysNaissance): self
    {
        $this->paysNaissance = $paysNaissance;

        return $this;
    }

    public function getNumeroSs(): ?string
    {
        return $this->numeroSs;
    }

    public function setNumeroSs(?string $numeroSs): self
    {
        $this->numeroSs = $numeroSs;

        return $this;
    }

    public function getNomUsage(): ?string
    {
        return $this->nomUsage;
    }

    public function setNomUsage(?string $nomUsage): self
    {
        $this->nomUsage = $nomUsage;

        return $this;
    }

    public function getDateExpirationTs(): ?\DateTimeInterface
    {
        return $this->dateExpirationTs;
    }

    public function setDateExpirationTs(?\DateTimeInterface $dateExpirationTs): self
    {
        $this->dateExpirationTs = $dateExpirationTs;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getDatePrevisEmbauche(): ?\DateTimeInterface
    {
        return $this->datePrevisEmbauche;
    }

    public function setDatePrevisEmbauche(?\DateTimeInterface $datePrevisEmbauche): self
    {
        $this->datePrevisEmbauche = $datePrevisEmbauche;

        return $this;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(string $poste): self
    {
        $this->poste = $poste;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getDelaiFormulaire(): ?\DateTimeInterface
    {
        return $this->delaiFormulaire;
    }

    public function setDelaiFormulaire(\DateTimeInterface $delaiFormulaire): self
    {
        $this->delaiFormulaire = $delaiFormulaire;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }
    
    public function getMdpGenere(): ?string
    {
        //////Création d'un mot de passe généré avec des caractères aléatoires//////
        // Liste des caractères possibles
        $cars = 'azertyiopqsdfghjklmwxcvbn0123456789';
        //la chaine de caractère qui sera remplie
        $mdp = '';
        $long = strlen($cars);

        srand((float) microtime() * 1000000);
        //Initialise le générateur de nombres aléatoires
        //Créer la chaine de caractère avec 12 caractères
        for ($i = 0; $i < Candidat::NB_CHAR_MDP; ++$i) {
            $mdp = $mdp.substr($cars, rand(0, $long - 1), 1);
        }
        $this->mdp = $mdp;
        //Assigne le mot de passe
        return $mdp;
    }

    public function setMdp(?string $mdp): self
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function getNumeroAgent(): ?string
    {
        return $this->numeroAgent;
    }

    public function setNumeroAgent(?string $numeroAgent): self
    {
        $this->numeroAgent = $numeroAgent;

        return $this;
    }

    public function getDebutCDD(): ?\DateTimeInterface
    {
        return $this->debutCDD;
    }

    public function setDebutCDD(?\DateTimeInterface $debutCDD): self
    {
        $this->debutCDD = $debutCDD;

        return $this;
    }

    public function getFinCDD(): ?\DateTimeInterface
    {
        return $this->finCDD;
    }

    public function setFinCDD(?\DateTimeInterface $finCDD): self
    {
        $this->finCDD = $finCDD;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getCoeffDeveloppe(): ?int
    {
        return $this->coeffDeveloppe;
    }

    public function setCoeffDeveloppe(?int $coeffDeveloppe): self
    {
        $this->coeffDeveloppe = $coeffDeveloppe;

        return $this;
    }

    public function getPtsGarantie(): ?int
    {
        return $this->ptsGarantie;
    }

    public function setPtsGarantie(?int $ptsGarantie): self
    {
        $this->ptsGarantie = $ptsGarantie;

        return $this;
    }

    public function getNiveauSalaire(): ?string
    {
        return $this->niveauSalaire;
    }

    public function setNiveauSalaire(?string $niveauSalaire): self
    {
        $this->niveauSalaire = $niveauSalaire;

        return $this;
    }

    public function getCoeffBase(): ?int
    {
        return $this->coeffBase;
    }

    public function setCoeffBase(?int $coeffBase): self
    {
        $this->coeffBase = $coeffBase;

        return $this;
    }

    public function getPtsCompetences(): ?int
    {
        return $this->ptsCompetences;
    }

    public function setPtsCompetences(?int $ptsCompetences): self
    {
        $this->ptsCompetences = $ptsCompetences;

        return $this;
    }

    public function getPeriodeEssai(): ?string
    {
        return $this->periodeEssai;
    }

    public function setPeriodeEssai(?string $periodeEssai): self
    {
        $this->periodeEssai = $periodeEssai;

        return $this;
    }

    public function getPtsExperience(): ?int
    {
        return $this->ptsExperience;
    }

    public function setPtsExperience(?int $ptsExperience): self
    {
        $this->ptsExperience = $ptsExperience;

        return $this;
    }

    public function getPrime(): ?string
    {
        return $this->prime;
    }

    public function setPrime(?string $prime): self
    {
        $this->prime = $prime;

        return $this;
    }

    public function isADiplome(): ?bool
    {
        return $this->aDiplome;
    }

    public function setADiplome(?bool $aDiplome): self
    {
        $this->aDiplome = $aDiplome;

        return $this;
    }

    public function getNationnalite(): ?string
    {
        return $this->nationnalite;
    }

    public function setNationnalite(?string $nationnalite): self
    {
        $this->nationnalite = $nationnalite;

        return $this;
    }

    public function getTypeNature(): ?int
    {
        return $this->typeNature;
    }

    public function setTypeNature(?int $typeNature): self
    {
        $this->typeNature = $typeNature;

        return $this;
    }

    public function getTypeReferentiel(): ?string
    {
        return $this->typeReferentiel;
    }

    public function setTypeReferentiel(?string $typeReferentiel): self
    {
        $this->typeReferentiel = $typeReferentiel;

        return $this;
    }

    public function isDejaComplete(): ?bool
    {
        return $this->dejaComplete;
    }

    public function setDejaComplete(?bool $dejaComplete): self
    {
        $this->dejaComplete = $dejaComplete;

        return $this;
    }

    public function isSupprime(): ?bool
    {
        return $this->supprime;
    }

    public function setSupprime(?bool $supprime): self
    {
        $this->supprime = $supprime;

        return $this;
    }

    public function getDateSuppression(): ?\DateTimeInterface
    {
        return $this->dateSuppression;
    }

    public function setDateSuppression(?\DateTimeInterface $dateSuppression): self
    {
        $this->dateSuppression = $dateSuppression;

        return $this;
    }

    public function getNumeroAgentManager(): ?string
    {
        return $this->numeroAgentManager;
    }

    public function setNumeroAgentManager(?string $numeroAgentManager): self
    {
        $this->numeroAgentManager = $numeroAgentManager;

        return $this;
    }

    public function getCle(): ?string
    {
        return $this->cle;
    }

    public function setCle(?string $cle): self
    {
        $this->cle = $cle;

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
            $document->setCandidat($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getCandidat() === $this) {
                $document->setCandidat(null);
            }
        }

        return $this;
    }

    public function getLien(): ?Lien
    {
        return $this->lien;
    }

    public function setLien(Lien $lien): self
    {
        // set the owning side of the relation if necessary
        if ($lien->getCandidat() !== $this) {
            $lien->setCandidat($this);
        }

        $this->lien = $lien;

        return $this;
    }

    public function getTypeCandidat(): ?TypeCandidat
    {
        return $this->typeCandidat;
    }

    public function setTypeCandidat(?TypeCandidat $typeCandidat): self
    {
        $this->typeCandidat = $typeCandidat;

        return $this;
    }

    public function getStatut(): array
    {
        return $this->statut;
    }

    public function setStatut(array $statut): self
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, MailEnvoye>
     */
    public function getMailEnvoyes(): Collection
    {
        return $this->mailEnvoyes;
    }

    public function addMailEnvoye(MailEnvoye $mailEnvoye): self
    {
        if (!$this->mailEnvoyes->contains($mailEnvoye)) {
            $this->mailEnvoyes->add($mailEnvoye);
            $mailEnvoye->setCandidat($this);
        }

        return $this;
    }

    public function removeMailEnvoye(MailEnvoye $mailEnvoye): self
    {
        if ($this->mailEnvoyes->removeElement($mailEnvoye)) {
            // set the owning side to null (unless already changed)
            if ($mailEnvoye->getCandidat() === $this) {
                $mailEnvoye->setCandidat(null);
            }
        }

        return $this;
    }

    public function getComplementAdresse(): ?string
    {
        return $this->complementAdresse;
    }

    public function setComplementAdresse(?string $complementAdresse): self
    {
        $this->complementAdresse = $complementAdresse;

        return $this;
    }
}
