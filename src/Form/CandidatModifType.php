<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Outils\Slugger;
use Cnam\FormTypeBundle\Type\NirType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Cnam\FormTypeBundle\Type\NirCleType;
class CandidatModifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
            $candidat = $options['candidat'];
            $tousEmploi = $options['tous_emplois']; 
            $tousSites = $options['tous_sites']; 
            $slugger = new Slugger(); 

            $builder
            ->add('nom')
            ->add('prenom'); 
            if (empty($tousEmploi)) {
                $builder->add('poste', null,[
                    'required' => true
                ]); 
            } else {
                $builder->add('poste', ChoiceType::class, [
                    'choices' => $tousEmploi,
                    'required' => true,
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
            ]);
            if ( strtoupper($candidat->getTypeCandidat()->getLibelle()) != "CDI" ) {
                $builder->add('finCDD', DateType::class, [
                    'label' => 'Date de fin du contrat',
                    'years' => range(date('Y'), date('Y') + 1),
                    'widget' => 'single_text',
                    'by_reference' => true,
                    'data' => $candidat->getFinCDD(),
                    'row_attr' => ['style' => 'display:none', 'id' => 'datesCDD']
                ]);
            }
  
            $builder->add('nomUsage', null, [
                'label' => "Nom d'usage",
            ])
            ->add('sexe', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                ],
            ])
            ->add('numeroSs', NirType::class, [ 
                'label' => 'Numéro de sécurité sociale',
                // 'data_mask' => true,
                'data' => $candidat->getNumeroSs()
            ])
            ->add('cle', NirCleType::class, [ 
                'label' => 'clé',
                'data' => $candidat->getCle()
            ])
            ->add('adresse')
            ->add('complementAdresse', null, [
                'label' => "Complément d'adresse",
            ])
            ->add('codePostal')
            ->add('ville')
            ->add('nationnalite', null, [
                'required' => true, 
                'label' => "Nationnalité", 
                // 'preferred_choices' => array('FR'),
                // 'choice_translation_locale' => null
            ])
            ->add('dateDeNaissance', BirthdayType::class, [
                'years' => range(date('Y') - 70, date('Y')),
                'format' => 'dd MM yyyy',
            ])
            ->add('paysNaissance', null, [
                'label' => 'Pays de naissance',
            ])
            ->add('departementNaissance')
            ->add('villeNaissance', null, [
                'label' => 'Ville de naissance',
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
                'row_attr' => ['style' => 'display:none', 'id' => 'dateExpirationTs']
            ])
            ;

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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
            'tous_sites' => [],
            'tous_emplois' => [],
            'candidat' => new Candidat(),
        ]);

        $resolver->setAllowedTypes('tous_sites', 'array');
        $resolver->setAllowedTypes('tous_emplois', 'array');
    }

    public function toLabel(string $libelle): string
    {
        $libelle[0] = strtoupper($libelle[0]);
        $libelle = str_replace('_', ' ', $libelle);

        return $libelle;
    }
}
