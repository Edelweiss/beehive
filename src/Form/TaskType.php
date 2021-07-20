<?php

namespace App\Form;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('category', ChoiceType::class, ['label' => 'Kategorie', 'choices' => self::getTaskCategories()])
            ->add('description', TextareaType::class, ['label' => 'Beschreibung'])
        ;
    }

    public static function getTaskCategories(){
        return array('apis' => 'APIS', 'biblio' => 'Biblio', 'bl' => 'BL', 'ddb' => 'DDB', 'hgv' => 'HGV', 'tm' => 'TM');
    }
    
    public static function getTaskCategoryKeys(){
        return array_keys(self::getTaskCategories());
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}