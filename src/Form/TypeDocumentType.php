<?php

namespace App\Form;

use App\Entity\TypeDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('obligatoire')
            ->add('multiple')
            ->add('format', ChoiceType::class, [
                'choices' => [
                    'pdf' => 'pdf',
                    'image' => 'image',
                ],
                "attr" => ["class" => "select2"]
            ])
            ->add("valider", SubmitType::class, [
                "label" => "Valider", 
                "attr" => ["class" => "btn btn-success"]
            ])
            ->add("validerEtSuivant", SubmitType::class, [
                "label" => "Valider et passer au suivant", 
                "attr" => ["class" => "btn btn-primary"]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TypeDocument::class,
        ]);
    }
}
