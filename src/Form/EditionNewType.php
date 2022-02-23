<?php

namespace App\Form;

use App\Entity\Edition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditionNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('sort', IntegerType::class, ['required' => false, 'label' => 'Sortierung'])
            ->add('title', TextType::class, ['required' => false, 'label' => 'Titel'])
            ->add('collection', TextType::class, ['required' => false, 'label' => 'Sammlung'])
            ->add('volume', TextType::class, ['required' => false, 'label' => 'Band'])
            ->add('remark', TextType::class, ['required' => false, 'label' => 'Bemerkung'])
            ->add('material', ChoiceType::class, ['label' => 'Material', 'choices' => ['Papyrus' => 'Papyrus', 'Ostrakon' => 'Ostrakon']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Edition::class,
        ]);
    }
}