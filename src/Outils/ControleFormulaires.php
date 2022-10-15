<?php

namespace App\Outils;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use App\Repository\DocumentRepository;
use App\Repository\TypeCandidatRepository;
use App\Service\GestionnaireFichiers;
use Doctrine\ORM\EntityManagerInterface;
use Flasher\Toastr\Prime\ToastrFactory;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\Test\FormInterface;
use Cnam\AdnBundle\Service\AdnService;

class ControleFormulaires
{
    private $toastr;
    protected $em;
    private $typeCandidatRepository; 
    private $gestionnaireFichier; 
    private $documentRepository; 
    private $candidatRepository;
    private $adnService; 
    private $numeroSiret; 

    public function __construct( DocumentRepository $documentRepository, 
                                ToastrFactory $toastr, 
                                EntityManagerInterface $em, 
                                TypeCandidatRepository $typeCandidatRepository, 
                                GestionnaireFichiers $gestionnaireFichier, 
                                CandidatRepository $candidatRepository, 
                                AdnService $adnService, 
                                $numeroSiret)
    {
        $this->em = $em;
        $this->toastr = $toastr;
        $this->typeCandidatRepository = $typeCandidatRepository;
        $this->gestionnaireFichier = $gestionnaireFichier; 
        $this->documentRepository = $documentRepository;
        $this->candidatRepository = $candidatRepository; 
        $this->adnService = $adnService; 
        $this->numeroSiret = $numeroSiret; 
    }

    /** retourne le numéro d'agent maximum en base de donnée +1 */
    public function checkNumeroAgent($numeroAgent)
    {
        $result = $this->candidatRepository->findByNumeroAgentTous($numeroAgent);
        while ( !empty($result) ) {
            $numeroAgent = '0'. strval(intval($numeroAgent)+1);
            $result = $this->candidatRepository->findByNumeroAgentTous($numeroAgent);
        }  
        return $numeroAgent;
    }

    /**focntion qui  vérifie si le numéro d'agent indiqué pour un candidat existe deja ou non */
    public function numAgentExisteDeja($numeroAgent, $candidat)
    {
        try {
            $agent = $this->adnService->findAllAgentsBy(['idAgent' => $numeroAgent . $this->numeroSiret]);
            return true; 
        } catch (\Throwable $th) {
            $agentBDD = $this->candidatRepository->findByNumeroAgentTous($numeroAgent);
            if(empty($agentBDD) || $agentBDD[0]->getId() == $candidat->getId()){
                return false; 
            }else {
                return true; 
            }
        } 
    }

    /**fonction qui controle que toutes les informations ont bien été renseignées avant la fin */
    public function controleAvantFin($candidat)
    {
        if($candidat->getNomUsage() == null
            || $candidat->getSexe() == null
            || $candidat->getNumeroSS() == null
            || $candidat->getAdresse() == null
            || $candidat->getCodePostal() == null
            || $candidat->getVille() == null
            || $candidat->getNationnalite() == null
            || $candidat->getDateDeNaissance() == null
            || $candidat->getPaysNaissance() == null
            || $candidat->getVilleNaissance() == null ){
                return false; 
            }else {
                return true; 
            }
    }

    /**fonction qui controle si tous les documents obligatoires sont présents pour un candidat */
    public function controleDocs(Candidat $candidat)
    {
        $documentsAFournir = $candidat->getTypeCandidat()->getDocumentsAFournir();
        $documentObligatoirePresent = false;
        foreach ($documentsAFournir as $typeDocument) {
            if ($typeDocument->getObligatoire()) {
                foreach ($typeDocument->getDocuments() as $document) {
                    if ($document->getCandidat()->getId() == $candidat->getId()) {
                        $documentObligatoirePresent = true;
                    }
                }
                if (false == $documentObligatoirePresent) {
                    $candidat->setStatut(4);
                    $this->toastr
                        ->info('Le document obligatoire '.$typeDocument->getLibelle()." n'as pas été déposé les informations ne peuvent donc pas êtres validées")
                        ->timeOut(500000)
                        ->progressBar()
                        ->flash()
                    ;
                }
            }
            $documentObligatoirePresent = false;
        }
    }

    /** fonction qui controle la conformité des dates dans le formulaire de création d'un candidat */
    public function controleDatesNouveau(Candidat $candidat, Form $form)
    {
        if ($form->get('datePrevisEmbauche')->getData() < (new \DateTime('now')) ||
        $form->get('delaiFormulaire')->getData() < (new \DateTime('now'))) {

            $this->toastr
            ->error("La date de prévision d'embauche et le delai de formulaire doivent êtres supérieur à la date du jour")
            ->timeOut(500000)
            ->progressBar()
            ->flash(); 
            return false;

        } elseif ($form->get('datePrevisEmbauche')->getData() < $form->get('delaiFormulaire')->getData()) {
        
            $this->toastr            
            ->error("La date de prévision d'embauche doit être supérieur à la date de délai du formulaire")
            ->timeOut(500000)
            ->progressBar()
            ->flash(); 
            return false;

        } elseif (!str_contains(strtoupper($this->typeCandidatRepository->findByLibelle($form->get('typeCandidat')->getData())[0]->getLibelle()), 'CDI') &&
                    ($form->get('datePrevisEmbauche')->getData() >= $form->get('finCDD')->getData())) {
            //Si la personne à une date de fin de contrat != CDI
            $this->toastr            
            ->error("La date de prévision d'embauche doit être inférieur à la date de fin du contrat")
            ->timeOut(500000)
            ->progressBar()
            ->flash(); 
            return false;

        }else {
            return true; 
        }
    }

    /** fonction qui controle la conformité des dates dans le formulaire de profil */
    public function controleDatesProfil(Candidat $candidat, Form $form)
    {
        if ($form->get('datePrevisEmbauche')->getData() < $form->get('delaiFormulaire')->getData()) {

            $this->toastr
            ->error("La date de prévision d'embauche doit être supérieur à la date de délai du formulaire")
            ->timeOut(500000)
            ->progressBar()
            ->flash(); 
            return false;

        } elseif (!str_contains(strtoupper($this->typeCandidatRepository->findByLibelle($form->get('typeCandidat')->getData())[0]->getLibelle()), 'CDI')  &&
                    ($form->get('datePrevisEmbauche')->getData() >= $form->get('finCDD')->getData())) {
            
                        $this->toastr
                        ->error("La date de prévision d'embauche doit être inférieur à la date de fin du contrat")
                        ->timeOut(500000)
                        ->progressBar()
                        ->flash();
                        return false;

        }else {
            return true; 
        }
    }

    /** fonction qui controle la conformité des dates dans le formulaire de contrôle d'info */
    public function controleDatesControleInfos(Candidat $candidat, Form $form)
    {
        if ($form->get('datePrevisEmbauche')->getData() < $form->get('delaiFormulaire')->getData()) {

            $this->toastr
            ->error("La date de prévision d'embauche doit être supérieur à la date de délai du formulaire")
            ->timeOut(500000)
            ->progressBar()
            ->flash();
            return false;

        }else {
            return true; 
        }
    }

    /**Si on a changer le mail initial vérifie que le nouveau mail n'existe pas déjà */
    public function controleChangementEmail($emailAvantModif, $emailApresModif)
    {
        if($emailAvantModif != $emailApresModif && $this->candidatRepository->findByEmail($emailApresModif) != null){
            $this->toastr
                ->error("Le mail que vous avez indiqué existe déjà. Veuillez en choisir un autre")
                ->timeOut(500000)
                ->progressBar()
                ->flash()
            ;
            return false; 
        }else {
            return true; 
        }
    }

    /**fonction qui donne une valeur à la fin de duree de contrat en fonction du type de candidat (type de contrat) */
    public function dureeContrat(Candidat $candidat){
        //Si la personne fait un stage ou un CDD son contrat à une durée que l'ont préciser.
        if (!str_contains(strtoupper($candidat->getTypeCandidat()->getLibelle()), 'CDI')) {
            $candidat->setDebutCDD($candidat->getDatePrevisEmbauche());
            //la fin du contrat se fait toute seul avec le formulaire
        } else {
            //Si la personne fait un CDI alors on a pas besoin de préciser le début et la fin du CDD
            $candidat->setDebutCDD(null);
            $candidat->setFinCDD(null);
        }
    }

    /** retourne tous les type de documents qu'un candidat à fourni */
    public function getTypeDocsFournis(Candidat $candidat){
        $documentsFournis = $this->documentRepository->findByCandidatId($candidat->getId());
        $typeDocumentsFournis = [];
        foreach ($documentsFournis as $document) {
            $typeDocumentsFournis[] = $document->getType()[0];
        }
        return $typeDocumentsFournis; 
    }
}
