<?php

namespace App\Form;

use App\Entity\Candidat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NouveauCandidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $candidat = $options['candidat'];
        $tousEmploi = $options['tous_emplois']; 
        $tousSites = $options['tous_sites']; 
        $tousServices = $options['tous_services']; 
        $tousTypesCandidat = $options['tous_types_candidats'];
        
        $choixTypeCandidat = [];
        foreach ($tousTypesCandidat as $typeCandidat) {
            $choixTypeCandidat[$typeCandidat->getLibelle()] = $typeCandidat->getLibelle();
        }

        $builder
        ->add('nom')
        ->add('prenom');

        if (!empty($tousEmploi)) {
            $builder->add('poste', ChoiceType::class, [
                'choices' => $tousEmploi,
            ]);
        } else {
            $builder->add('poste'); 
        }

        if (!empty($tousSites)) {
            $builder->add('site', ChoiceType::class, [
                'choices' => $tousSites,
            ]); 
        } else {
            $builder->add('site'); 
        }

        $builder->add('email', EmailType::class)
        ->add('typeCandidat', ChoiceType::class, [
            'choices' => $choixTypeCandidat,
            'mapped' => false,
        ])
        ->add('datePrevisEmbauche', DateType::class, [
            'label' => "Date prévisionnelle d'embauche",
            'years' => range(date('Y'), date('Y') + 1),
            'widget' => 'single_text',
            'data' => new \DateTime('now'),
            'by_reference' => true,
        ])
        ->add('delaiFormulaire', DateType::class, [
            'label' => 'Formulaire à renseigner avant le : ',
            'years' => range(date('Y'), date('Y') + 1),
            'data' => new \DateTime('now'),
            'widget' => 'single_text',
            'by_reference' => true,
        ])
        ->add('finCDD', DateType::class, [
            'label' => 'Date de fin du contrat',
            'years' => range(date('Y'), date('Y') + 1),
            'row_attr' => ['style' => 'display:none', 'id' => 'datesCDD'], 
            'widget' => 'single_text',
            'by_reference' => true,
        ]); 

        if (!empty($tousServices)) {
            $builder->add('service', ChoiceType::class, [
                'label' => 'Service',
                'choices' => $tousServices,
            ]); 
        } else {
            $builder->add('service'); 
        }

        $builder->add('periodeEssai', null, [
            'label' => "Période d'essai",
            'required' => false,
        ])
        ->add('niveauSalaire', ChoiceType::class, [
            'label' => 'Niveau de salaire',
            'choices' => $this->getChoices(),
            'required' => false,
            'data' => '2',
            ])
        ->add('coeffBase', null, [
            'label' => 'Coefficient de base',
            'required' => false,
        ])
        ->add('coeffDevpe', null, [
            'label' => 'Coefficient développé',
            'required' => false,
        ])
        ->add('ptsGarantie', null, [
            'label' => 'Points de garantie',
            'required' => false,
        ])
        ->add('ptsCompetences', null, [
            'label' => 'Points de compétences',
            'required' => false,
        ])
        ->add('ptsExperiences', null, [
            'label' => "Points d'experience",
            'required' => false,
        ])
        ->add('prime', null, [
            'label' => 'Prime',
            'required' => false
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
            'tous_types_candidats' => [],
            'tous_sites' => [],
            'tous_emplois' => [],
            'tous_services' => [],
            'candidat' => new Candidat(),

        ]);
        $resolver->setAllowedTypes('tous_types_candidats', 'array');
        $resolver->setAllowedTypes('tous_sites', 'array');
        $resolver->setAllowedTypes('tous_emplois', 'array');
        $resolver->setAllowedTypes('tous_services', 'array');
    }

    private function getChoices()
    {
        $choices = Candidat::NIVEAU_COEFF;
        $output = [];
        foreach ($choices as $k => $v) {
            $output[$k] = $k;
        }

        return $output;
    }
}
