<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Entity\TypeCandidat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            // ->add('nomUsage')
            // ->add('sexe')
            // ->add('numeroSs')
            // ->add('cle')
            // ->add('adresse')
            // ->add('complementAdresse')
            // ->add('codePostal')
            // ->add('ville')
            // ->add('dateDeNaissance')
            // ->add('villeNaissance')
            // ->add('departementNaissance')
            // ->add('paysNaissance')
            // ->add('nationnalite')
            // ->add('dateExpirationTs')
            // ->add('statut')
            ->add('email', EmailType::class)
            ->add('poste')
            ->add('service')
            ->add('site')
            ->add('datePrevisEmbauche', null, [
                "widget" => "single_text", 
                "by_reference" => true, 
                "label" => "Date prévisionnelle d'embauche"
            ])
            ->add('delaiFormulaire', null, [
                "widget" => "single_text", 
                "by_reference" => true, 
                "label" => "Date d'éxpiration du formulaire"
            ])
            // ->add('mdp')
            // ->add('numeroAgent')
            ->add('debutCDD', null, [
                "widget" => "single_text", 
                "by_reference" => true, 
                "label" => "Date de début du contrat"
            ])
            ->add('finCDD', null, [
                "widget" => "single_text", 
                "by_reference" => true, 
                "label" => "Date de fin du contrat"
            ])
            // ->add('coeffDevpe')
            // ->add('ptsGarantie')
            // ->add('niveauSalaire')
            // ->add('coeffBase')
            // ->add('ptsCompetences')
            // ->add('ptsExperiences')
            ->add('periodeEssai', null, [
                "label" => "Période d'essai"
            ])
            ->add('prime')
            // ->add('aDiplome')
            // ->add('typeNature')
            // ->add('typeReferentiel')
            // ->add('dejaComplete')
            // ->add('supprime'), null, 
            // ->add('dateSuppression')
            // ->add('numeroAgentManager')
            // ->add('agentCreateur')
            ->add('typeCandidat', EntityType::class, [
                "class" => TypeCandidat::class,
                "label" => "Type de contrat", 
                "choice_label" => "libelle"
            ])
            ->add("valider", SubmitType::class, [
                "label" => "Valider", 
                "attr" => ["class" => "btn btn-success"]
            ])
            ->add("validerEtSuivant", SubmitType::class, [
                "label" => "Valider et passer au suivant", 
                "attr" => ["class" => "btn btn-primary"]
            ])
            // ->add('lien')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
        ]);
    }
}
