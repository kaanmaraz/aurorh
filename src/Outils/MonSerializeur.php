<?php

namespace App\Outils;

use App\Repository\LienRepository; 
use App\Repository\TypeCandidatRepository; 
use DateTimeZone;
class MonSerializeur
{
    private $typeCandidatRepository; 
    private $lienRepository; 

    public function __construct(TypeCandidatRepository $typeCandidatRepository, LienRepository $lienRepository){

        $this->typeCandidatRepository = $typeCandidatRepository; 
        $this->lienRepository = $lienRepository; 
    }

    public function deserializeDates($candidat, $candidatDecode)
    {
        if (isset($candidatDecode->candidat->dateDeNaissance) && null != $candidatDecode->candidat->dateDeNaissance) {
            $candidat->setDateDeNaissance(date_create_from_format('d-m-Y', $candidatDecode->candidat->dateDeNaissance, new DateTimeZone('Europe/Berlin')));
        }
        if (isset($candidatDecode->candidat->delaiFormulaire) && null != $candidatDecode->candidat->delaiFormulaire) {
            $candidat->setDelaiFormulaire(date_create_from_format('d-m-Y', $candidatDecode->candidat->delaiFormulaire, new DateTimeZone('Europe/Berlin')));
        }
        if (isset($candidatDecode->candidat->datePrevisEmbauche) && null != $candidatDecode->candidat->datePrevisEmbauche) {
            $candidat->setDatePrevisEmbauche(date_create_from_format('d-m-Y', $candidatDecode->candidat->datePrevisEmbauche, new DateTimeZone('Europe/Berlin')));
        }
        if (isset($candidatDecode->candidat->dateExpirationTs) && null != $candidatDecode->candidat->dateExpirationTs) {
            $candidat->setDateExpirationTs(date_create_from_format('d-m-Y', $candidatDecode->candidat->dateExpirationTs, new DateTimeZone('Europe/Berlin')));
        }
        return $candidat; 
    }

    public function deserializeRelations($candidat, $candidatDecode)
    {
        $candidat->setTypeCandidat($this->typeCandidatRepository->findById($candidatDecode->candidat->typeCandidat->id)[0]);
        $candidat->setLien($this->lienRepository->findOneById($candidatDecode->candidat->lien->id));

        return $candidat; 
    }

        //Tous les attributs suivants ne sont pas présents coté FrontOffice (Formulaire OVH)
    //Lorsqu'on récupère ils sont donc mis à null par défaut. On leur redonne donc leurs valeurs initiales. 
    public function valeursPrecedentesAbsentFrontOffice($candidat, $candidatAvantModif)
    {
        if (null != $candidatAvantModif) {
            $candidat->setNumeroAgent($candidatAvantModif->getNumeroAgent());
            $candidat->setDebutCDD($candidatAvantModif->getDebutCDD());
            $candidat->setFinCDD($candidatAvantModif->getFinCDD());
            $candidat->setPeriodeEssai($candidatAvantModif->getPeriodeEssai());
            $candidat->setService($candidatAvantModif->getService());
            $candidat->setNiveauSalaire($candidatAvantModif->getNiveauSalaire());
            $candidat->setCoeffDevpe($candidatAvantModif->getCoeffDevpe());
            $candidat->setPtsGarantie($candidatAvantModif->getPtsGarantie());
            $candidat->setCoeffBase($candidatAvantModif->getCoeffBase());
            $candidat->setPtsCompetences($candidatAvantModif->getPtsCompetences());
            $candidat->setPtsExperiences($candidatAvantModif->getPtsExperiences());
            $candidat->setPrime($candidatAvantModif->getPrime());
            $candidat->setTypeNature($candidatAvantModif->getTypeNature());
            $candidat->setTypeReferentiel($candidatAvantModif->getTypeReferentiel());
            $candidat->setNumeroAgentManager($candidatAvantModif->getNumeroAgentManager());
            $candidat->setAgentCreateur($candidatAvantModif->getAgentCreateur());
            $candidat->setTypeNature($candidatAvantModif->getTypeNature());
            $candidat->setDejaComplete($candidatAvantModif->getDejaComplete());
        }
        return $candidat; 
    }
}
