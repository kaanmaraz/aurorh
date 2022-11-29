<?php

namespace App\Form;

use App\Entity\Candidat;
use App\Entity\TypeCandidat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidatType extends AbstractType
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
            ->add('prenom')
            ->add('nomUsage')
            ->add('sexe')
            ->add('numeroSs')
            ->add('cle')
            ->add('adresse')
            ->add('complementAdresse')
            ->add('codePostal')
            ->add('ville')
            ->add('dateDeNaissance', null, [
                'label' => "Expiration titre de séjour",
                'widget' => 'single_text',
                'by_reference' => true,
            ])
            ->add('villeNaissance')
            ->add('departementNaissance')
            ->add('paysNaissance')
            ->add('nationnalite')
            ->add('dateExpirationTs', null, [
                'label' => "Expiration titre de séjour",
                'widget' => 'single_text',
                'by_reference' => true,
            ])
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
            // ->add('delaiFormulaire', null, [
            //     "widget" => "single_text", 
            //     "by_reference" => true, 
            //     "label" => "Date d'éxpiration du formulaire"
            // ])
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
                    'label_attr' => ['style' => 'width:70%; ']
                ]);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidat::class,
        ]);
    }
}
