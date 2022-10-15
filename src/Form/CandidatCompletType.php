<?php

namespace App\Form;

use App\Entity\Candidat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidatCompletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $candidat = $options['candidat'];
        $tousServices = $options['tous_services']; 
        $maxNbAgent = $options['max_numero_agent']; 
        $tousTypesNature =  $options['tous_types_nature'];
            $builder
            ->add('numeroAgent', null, [
                'label' => $label = $candidat->getNumeroAgent() == null ? "Numéro d'agent (la valeur proposée est : " . $maxNbAgent . ')' : "Numéro d'agent (la valeur indiquée est : " . $candidat->getNumeroAgent() . ')',
                'required' => false,
                'data' => $candidat->getNumeroAgent() == null ? $maxNbAgent : $candidat->getNumeroAgent()
            ]); 
            if (empty($tousServices)) {
                $builder->add('service');
            } else {
                $builder
                    ->add('service', ChoiceType::class, [
                    'label' => 'Service',
                    'choices' => $tousServices,
                    'required' => true,
                    'data' => $candidat->getService(),
                ]); 
            }

            $builder->add('numeroAgentManager', TextType::class, [
                'label' => "Numero d'agent du Manager (veuillez taper au moins 3 lettres du nom du manager et séléctionner le)",
                'required' => true,
                'data' => $candidat->getNumeroAgentManager(),
                'attr' => ["onkeyup" => "getManagers()"]
            ]);

            if ( empty($tousTypesNature) ) {
                $builder->add('typeNature'); 
            } else {
                $builder->add('typeNature', ChoiceType::class, [
                    'label' => 'Nature',
                    'choices' => $tousTypesNature,
                    'required' => true,
                    'attr' => ["onchange" => "getReferentiel()"]
                ]);
            }
            
 
            $builder->add('typeReferentiel', null, [
                'label' => 'Referentiel',
                'required' => true,
            ])
            ->add('periodeEssai', null, [
                'label' => "Période d'essai",
                'required' => true,
                'data' => $candidat->getPeriodeEssai(),
            ])
            ->add('niveauSalaire', ChoiceType::class, [
                'label' => 'Niveau de salaire',
                'choices' => $this->getChoices(),
                'required' => true,
                'data' => $candidat->getNiveauSalaire(),
                'attr' => ["onchange" => "changeSalaire('candidat_complet')"]
            ])
            ->add('coeffBase', null, [
                'label' => 'Coefficient de base',
                'required' => true,
                'data' => $candidat->getCoeffBase(),
            ])
            ->add('coeffDevpe', null, [
                'label' => 'Coefficient développé',
                'required' => true,
                'data' => $candidat->getCoeffDevpe(),
            ])
            ->add('ptsGarantie', null, [
                'label' => 'Points de garantie',
                'required' => true,
                'data' => $candidat->getPtsGarantie(),
            ])
            ->add('ptsCompetences', null, [
                'label' => 'Points de compétences',
                'required' => true,
                'data' => $candidat->getPtsCompetences(),
            ])
            ->add('ptsExperiences', null, [
                'label' => "Points d'experience",
                'required' => true,
                'data' => $candidat->getPtsExperiences(),
            ])
            ->add('prime', null, [
                'label' => 'Prime',
                'data' => $candidat->getPrime(),
            ])
            ;

            if (!str_contains(strtoupper($candidat->getTypeCandidat()->getLibelle()), 'CDI')) {
                $builder->add('debutCDD', DateType::class, [
                        'label' => 'Date de début du contrat',
                        'years' => range(date('Y'), date('Y') + 1),
                        'format' => 'dd MM yyyy',
                        'data' => $candidat->getDatePrevisEmbauche(),
                        ])
                    ->add('finCDD', DateType::class, [
                        'label' => 'Date de fin du contrat',
                        'years' => range(date('Y'), date('Y') + 1),
                        'format' => 'dd MM yyyy',
                        'data' => $candidat->getFinCDD(),
                        ]);             
            }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
            'tous_services' => [], 
            'max_numero_agent' => '0', 
            'tous_types_nature'=>[],
            'candidat' => new Candidat(),

        ]);
        $resolver->setAllowedTypes('tous_types_nature', 'array');
        $resolver->setAllowedTypes('tous_services', 'array');
        $resolver->setAllowedTypes('max_numero_agent', 'string');

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
