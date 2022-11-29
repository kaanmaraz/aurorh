<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Entity\TypeDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints\File;

class FormulaireCandidatType extends AbstractType
{

    public const IMAGE_INPUT_ATTRIBUTS = ['accept' => 'image/*'];
    public const PDF_INPUT_ATTRIBUTS = ['accept' => 'application/pdf'];
    public const FORMATS_ATTRIBUTS = [
        'image' => ['accept' => 'image/*'], 
        'pdf' => ['accept' => 'application/pdf']
    ]; 

    public const MIME_TYPE_PDF = ['application/pdf','application/x-pdf']; 
    public const MIME_TYPE_IMAGE = ['image/*']; 
    public const FORMAT_MIME_TYPES = [
        'pdf' => ['application/pdf','application/x-pdf'], 
        'image' =>  ['image/*',],
    ];  

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $candidat = $builder->getData(); 

        $builder
            ->add('nom')
            ->add('nomUsage', null, [
                "label" => "Nom d'usage"
            ])
            ->add('prenom')
            ->add('sexe', ChoiceType::class,[
                'choices' => ["Homme" => "Homme", "Femme" => "Femme", "Autre" => "Autre"]
            ])
            ->add('numeroSs', null, [
                "label" => "Numéro de sécurité sociale"
            ])
            ->add('cle', null, [
                "help" => "Les 2 numéros à la fin du numéro de sécurité sociale"
            ])
            ->add('adresse', ChoiceType::class, [
                'help' => "Commencez à taper votre adresse et choisissez la dans la liste", 
                "choices" => $options = $builder->getData()->getAdresse() ? [$builder->getData()->getAdresse() => $builder->getData()->getAdresse()] : [],
                "data" => $builder->getData()->getAdresse()
            ])
            ->add('complementAdresse')
            ->add('codePostal')
            ->add('ville')
            ->add('dateDeNaissance',null, [
                'label' => "Date de naissance",
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('villeNaissance', ChoiceType::class, [
                "label" => "Ville de naissance",
                "choices" => $options = $builder->getData()->getVilleNaissance() ? [$builder->getData()->getVilleNaissance() => $builder->getData()->getVilleNaissance()] : [],
                "data" => $builder->getData()->getVilleNaissance()
            ])
            ->add('departementNaissance', ChoiceType::class, [
                "label" => "Département de naissance",
                "choices" => $options = $builder->getData()->getDepartementNaissance() ? [$builder->getData()->getDepartementNaissance() => $builder->getData()->getDepartementNaissance()] : [],
                "data" => $builder->getData()->getDepartementNaissance()
            ])
            ->add('paysNaissance', ChoiceType::class, [
                "label" => "Pays de naissance",
                "choices" => $options = $builder->getData()->getPaysNaissance() ? [$builder->getData()->getPaysNaissance() => $builder->getData()->getPaysNaissance()] : [],
                "data" => $builder->getData()->getPaysNaissance()
            ])
            ->add('nationnalite', ChoiceType::class, [
                "choices" => $options = $builder->getData()->getNationnalite() ? [$builder->getData()->getNationnalite() => $builder->getData()->getNationnalite()] : [],
                "data" => $builder->getData()->getNationnalite()
            ])
            ->add('dateExpirationTs',null, [
                'label' => "Expiration titre de séjour",
                'widget' => 'single_text',
                'by_reference' => true,
                'help' => "Si vous n'avez pas la nationnalité française"
            ])
        ;
        $builder->get('adresse')->resetViewTransformers(); 
        $builder->get('villeNaissance')->resetViewTransformers(); 
        $builder->get('departementNaissance')->resetViewTransformers(); 
        $builder->get('paysNaissance')->resetViewTransformers(); 
        $builder->get('nationnalite')->resetViewTransformers(); 

        /** @var TypeDocument $typeDocument */ 
        foreach ($candidat->getTypeCandidat()->getDocumentsAFournir() as $typeDocument) { 
            if (!$typeDocument->isMultiple()) {
                $builder->add($typeDocument->getSlug(), FileType::class, [
                    'required' => $typeDocument->isObligatoire(),
                    'label' => $typeDocument->getLibelle(),
                    'attr' =>  $this::FORMATS_ATTRIBUTS[$typeDocument->getFormat()] ,
                    'row_attr' => ["class" => "col-md-8"],
                    'constraints' => [
                        new File([
                            'maxSize' => '2048k',
                            'maxSizeMessage' => 'Veuillez télécharger un document avec une taille inférieure à 2Mo',
                            'mimeTypes' => $this::FORMAT_MIME_TYPES[$typeDocument->getFormat()],
                            'mimeTypesMessage' => 'Veuillez télécharger un document en format ' . $typeDocument->getFormat(),
                        ]),
                    ],
                    'mapped' => false,
                ]);
            } else {
                $builder->add($typeDocument->getSlug(), CollectionType::class, [
                    'entry_type' => FileType::class,
                    'entry_options' => [
                        'attr' => $this::FORMATS_ATTRIBUTS[$typeDocument->getFormat()],
                        'constraints' => [
                            new File([
                                'maxSize' => '2048k',
                                'maxSizeMessage' => 'Veuillez télécharger un document avec une taille inférieure à 2Mo',
                                'mimeTypes' => $this::FORMAT_MIME_TYPES[$typeDocument->getFormat()],
                                'mimeTypesMessage' => 'Veuillez télécharger un document en format image ' . $typeDocument->getFormat(),
                            ]),
                        ],
                        "row_attr" => ["class" => "col-md-11 element-collection-file"], 
                    ],
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'label' => $typeDocument,
                    'required' => false,
                    'mapped' => false,
                    'attr' => ["class" => "collection"], 
                    'label_attr' => ['style' => 'width:70%; font-weight:bold']
                ]);
            }
        }

        $builder->add("valider", SubmitType::class, [
            'attr' => ["class" => "btn btn-lg btn-primary"]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
        ]);
    }
}
