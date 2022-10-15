<?php

namespace App\Form;

use App\Entity\Candidat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use App\Service\GestionnaireExcel;
use App\Outils\Slugger;
use Cnam\FormTypeBundle\Type\NirType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Cnam\FormTypeBundle\Type\NirCleType;
class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

            $candidat = $options['candidat'];
            $tousEmploi = $options['tous_emplois']; 
            $tousSites = $options['tous_sites']; 
            $tousServices = $options['tous_services']; 
            $tousTypesCandidat = $options['tous_types_candidats']; 
            $slugger = new Slugger(); 

            if ($candidat->getStatut() >= 2) {
                $requiredDisabled2 = true;
            } else {
                $requiredDisabled2 = false;
            }
            $requiredDisabled3 = false;

        $choixTypeCandidat = [];
        foreach ($tousTypesCandidat as $typeCandidat) {
            $choixTypeCandidat[$typeCandidat->getLibelle()] = $typeCandidat->getLibelle();
        }


            $builder
            ->add('nom')
            ->add('prenom'); 
            if (empty($tousEmploi)) {
                $builder->add('poste'); 
            } else {
                $builder->add('poste', ChoiceType::class, [
                    'choices' => $tousEmploi,
                ]); 
            }

            if (empty($tousSites)) {
                $builder->add('site');
            } else {
                $builder->add('site', ChoiceType::class, [
                    'choices' => $tousSites,
                ]);
            }
            $builder->add('email', EmailType::class, [
                'disabled' => true,
            ])
            ->add('typeCandidat', ChoiceType::class, [
                'choices' => $choixTypeCandidat,
                'mapped' => false,
                'data' => $candidat->getTypeCandidat()->getLibelle(),
                'disabled' => $candidat->getStatut() != 0,
            ])
            ->add('datePrevisEmbauche', DateType::class, [
                'label' => "Date prévisionnelle d'embauche",
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('delaiFormulaire', DateType::class, [
                'label' => 'Formulaire à renseigner avant le : ',
                'years' => range(date('Y'), date('Y') + 1),
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('finCDD', DateType::class, [
                'label' => 'Date de fin du contrat',
                'years' => range(date('Y'), date('Y') + 1),
                'widget' => 'single_text',
                'by_reference' => true,
                'data' => $candidat->getFinCDD(),
                'row_attr' => ['style' => 'display:none', 'id' => 'datesCDD']
            ])
            ->add('nomUsage', null, [
                'label' => "Nom d'usage",
            ])            
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
                'required' => $requiredDisabled2,
                
            ])
            ->add('numeroSs', NirType::class, [ 
                'label' => 'Numéro de sécurité sociale',
                'required' => $requiredDisabled2,
                
            ])
            ->add('cle', NirType::class, [ 
                'label' => 'clé',
                'required' => $requiredDisabled2,
                
            ])
            ->add('adresse', null, [
                'required' => $requiredDisabled2,
                
            ])
            ->add('complementAdresse', null, [
                'label' => "Complément d'adresse",
                
            ])
            ->add('codePostal', null, [
                'required' => $requiredDisabled2,
                
            ])
            ->add('ville', null, [
                'required' => $requiredDisabled2,
                
            ])
            ->add('nationnalite', null, [
                // 'required' => true, 
                'label' => "Nationnalité", 
                // 'preferred_choices' => array('FR'),
                // 'choice_translation_locale' => null,
                'required' => $requiredDisabled2,
                
            ])
            ->add('dateDeNaissance', BirthdayType::class, [
                'years' => range(date('Y') - 70, date('Y')),
                'format' => 'dd MM yyyy',
                'required' => $requiredDisabled2,
                
            ])
            ->add('paysNaissance', null, [
                'label' => 'Pays de naissance',
                'required' => $requiredDisabled2,
                
            ])
            ->add('departementNaissance', null, [
                'required' => false,
                
            ])
            ->add('villeNaissance', null, [
                'label' => 'Ville de naissance',
                'required' => $requiredDisabled2,
                
            ])
            ->add('tdsChoix', ChoiceType::class, [
                'label' => "Présence d'un titre de séjour",
                'choices' => [
                    'Oui' => 'Oui', 
                    'Non' => 'Non'
                ],
                'empty_data' => 'Non', 
                'mapped' => false,
            ])
            ->add('dateExpirationTs', DateType::class, [
                'label' => "Date d'expiration titre de séjour",
                'years' => range(date('Y') - 1, date('Y') + 20),
                'format' => 'dd MM yyyy',
                'row_attr' => ['style' => 'display:none', 'id' => 'dateExpirationTs'],
                'required' => $requiredDisabled2,
                    
            ]); 
            if ($candidat->getStatut() >= 2) {
                $attributsPourinputPhoto = ['accept' => 'image/*'];
                $attributsPourinputFile = ['accept' => 'application/pdf'];
                $attributsFormat = [
                    'jpeg' => $attributsPourinputPhoto, 
                    'pdf' => $attributsPourinputFile
                ]; 
                
                $mimeTypePdf = ['application/pdf','application/x-pdf',]; 
                $mimeTypeImage = ['image/*',]; 
                $mimeTypes = [
                    'jpeg' => $mimeTypeImage, 
                    'pdf' => $mimeTypePdf,
                ];  

                foreach ($candidat->getTypeCandidat()->getDocumentsAFournir() as $document) {
                    $libelle = $slugger->toSlug($document->getLibelle()); 
                    $label = $document->getLibelle();

                    if (!$document->getMultiple()) {
                        $builder->add($libelle, FileType::class, [
                            'required' => false,
                            'label' => $label,
                            'attr' =>  $attributsFormat[$document->getFormat()] ,
                            'constraints' => [
                                new File([
                                    'maxSize' => '2048k',
                                    'maxSizeMessage' => 'Veuillez télécharger un document avec une taille inférieure à 2Mo',
                                    'mimeTypes' => $mimeTypes[$document->getFormat()],
                                    'mimeTypesMessage' => 'Veuillez télécharger un document en format ' . $document->getFormat(),
                                ]),
                            ],
                            'mapped' => false,
                            'row_attr' => ['style' => 'display:none']
                        ]);
                    } else {
                        $builder->add($libelle, CollectionType::class, [
                            'entry_type' => FileType::class,
                            'entry_options' => [
                                'attr' => $attributsPourinputFile,
                                'constraints' => [
                                    new File([
                                        'maxSize' => '2048k',
                                        'maxSizeMessage' => 'Veuillez télécharger un document avec une taille inférieure à 2Mo',
                                        'mimeTypes' => $mimeTypes[$document->getFormat()],
                                        'mimeTypesMessage' => 'Veuillez télécharger un document en format image ' . $document->getFormat(),
                                    ]),
                                ],
                            ],
                            'prototype' => true,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'by_reference' => false,
                            'label' => $label,
                            'required' => false,
                            'mapped' => false,
                            'row_attr' => ['style' => 'display:none']
                        ]);
                    }
                }
            }
            if (empty($tousServices)) {
                $builder->add('service');
            } else {
                $builder
                ->add('service', ChoiceType::class, [
                    'label' => 'Service',
                    'choices' => $tousServices,
                    'required' => $requiredDisabled3,
                    
                    'data' => $candidat->getService(),
                ]); 
            }
            
            $builder->add('periodeEssai', null, [
                'label' => "Période d'essai",
                'required' => false,
                'data' => $candidat->getPeriodeEssai(),
            ])
            ->add('niveauSalaire', ChoiceType::class, [
                'label' => 'Niveau de salaire',
                'choices' => $this->getChoicesNvSalaire(),
                'required' => $requiredDisabled3,
                
                'data' => $candidat->getNiveauSalaire(),
            ])
            ->add('coeffBase', null, [
                
                'required' => $requiredDisabled3,
                'data' => $candidat->getCoeffBase(),
            ])
            ->add('coeffDevpe', null, [
                
                'required' => $requiredDisabled3,
                'data' => $candidat->getCoeffDevpe(),
            ])
            ->add('ptsGarantie', null, [
                
                'required' => $requiredDisabled3,
                'data' => $candidat->getPtsGarantie(),
            ])
            ->add('ptsCompetences', null, [
                'label' => 'Points de compétences',
                
                'required' => $requiredDisabled3,
                'data' => $candidat->getPtsCompetences(),
            ])
            ->add('ptsExperiences', null, [
                'label' => "Points d'experience",
                
                'required' => $requiredDisabled3,
                'data' => $candidat->getPtsExperiences(),
            ])
            ->add('prime', null, [
                'label' => 'Prime',
                'data' => $candidat->getPrime(),
            ])
            ->add('finCDD', DateType::class, [
                'label' => 'Date de fin du contrat',
                'years' => range(date('Y'), date('Y') + 1),
                'format' => 'dd MM yyyy',
                'data' => $candidat->getFinCDD(),
                'row_attr' => ['style' => 'display:none', 'id' => 'datesCDD']
            ]); 
            if($candidat->getLien() == null){
                $builder->add('statut', ChoiceType::class, [
                    'choices' => $this->getChoicesStatutSansMailEnvoye($candidat->getStatut()),
                ])
                ;
            }else {
                $builder->add('statut', ChoiceType::class, [
                    'choices' => $this->getChoicesStatut($candidat->getStatut()),
                ])
                ;
            }
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

    private function getChoicesStatut(int $statut)
    {
        $choices = Candidat::STATUT_LIBELLE;
        $output = [];
        foreach ($choices as $k => $v) {
                $output[$v] = $k;
        }

        return $output;
    }

    private function getChoicesStatutSansMailEnvoye(int $statut)
    {
        $choices = Candidat::STATUT_LIBELLE;
        $output = [];
        foreach ($choices as $k => $v) {
                if($k !=1){
                    $output[$v] = $k;
                }
        }

        return $output;
    }

    private function getChoicesNvSalaire()
    {
        $choices = Candidat::NIVEAU_COEFF;
        $output = [];
        foreach ($choices as $k => $v) {
            $output[$k] = $k;
        }

        return $output;
    }


    public function toLabel(string $libelle): string
    {
        $libelle[0] = strtoupper($libelle[0]);
        $libelle = str_replace('_', ' ', $libelle);

        return $libelle;
    }
}
