<?php

namespace App\Form;

use App\Entity\IndexEntry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IndexEntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('type', ChoiceType::class, ['label' => 'Kategorie', 'choices' => self::getIndexTypes()])
            ->add('topic', ChoiceType::class, ['label' => 'Thema', 'choices' => self::getIndexTopics()])
            ->add('phrase', TextareaType::class, ['label' => 'Beschreibung'])
        ;
    }

    public static function getIndexTypes(){
        return array('Neues Wort' => 'Neues Wort', 'Ghostword' => 'Ghostword');
    }

    public static function getIndexTopics(){
        return array('Personennamen' => 'Personennamen', 'Könige, Kaiser, Konsuln' => 'Könige, Kaiser, Konsuln', 'Geographisches und Topographisches' => 'Geographisches und Topographisches', 'Monate und Tage' => 'Monate und Tage', 'Religion' => 'Religion', 'Zivil- und Militärverwaltung' => 'Zivil- und Militärverwaltung', 'Steuern' => 'Steuern', 'Berufsbezeichnungen' => 'Berufsbezeichnungen', 'Allgemeiner Wortindex' => 'Allgemeiner Wortindex');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => IndexEntry::class,
        ]);
    }
}