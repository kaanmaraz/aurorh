<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AddPJMailTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pj', FileType::class, [
            'required' => false,
            'label' => "Ajouter une pièce jointe au mail",
            'attr' =>  ['accept' => 'image/*,application/pdf, application/zip'] ,
            'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez télécharger un document avec une taille inférieure à 2Mo',
                    'mimeTypes' => ['application/pdf', 'image/*', 'application/zip'],
                    'mimeTypesMessage' => 'Veuillez télécharger un document dans les formats suivants (pdf, image ou zip)',
                ]),
            ],
            'required' => true
        ])
        ->add("ajouter", SubmitType::class, [
            "label" => "Ajouter", 
            "attr" => ["class" => "btn btn-xs btn-primary", "style" => "float:right"]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
