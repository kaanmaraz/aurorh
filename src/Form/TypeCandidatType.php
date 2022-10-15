<?php

namespace App\Form;

use App\Entity\TypeCandidat;
use App\Entity\TypeDocument;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Outils\Slugger;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class TypeCandidatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', null, [
                'label' => 'Libelle',
            ])
            ->add("documentsAFournir", EntityType::class, [
                "label" => "Documents à fournir (Vous pouvez en ajouter plusieurs)",
                "choice_label" => "libelle", 
                "class" => TypeDocument::class, 
                "multiple" => true, 
                "attr" => ["class" => "select2 multiple"], 
                "mapped" => true, 
                "by_reference" => false, 
                "required" => false
            ])
            ->add("valider", SubmitType::class, [
                "label" => "Valider", 
                "attr" => ["class" => "btn btn-success"]
            ])
            ->add("validerEtSuivant", SubmitType::class, [
                "label" => "Valider et passer au suivant", 
                "attr" => ["class" => "btn btn-primary"]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TypeCandidat::class,
        ]);
    }
}
