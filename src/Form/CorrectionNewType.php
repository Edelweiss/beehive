<?php

namespace App\Form;

use App\Entity\Correction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CorrectionNewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('compilationPage', TextType::class, ['required' => false, 'label' => 'Seite'])
            ->add('compilationIndex', TextType::class, ['required' => false, 'label' => 'Nummer'])
            ->add('text', TextType::class, ['attr' => ['wizard-url' => $options['attr']['wizardUrl']]])
            ->add('position', TextType::class, ['required' => false, 'label' => 'Zeile'])
            ->add('description', TextareaType::class, ['label' => 'Eintrag'])
            ->add('source', TextType::class, ['required' => false, 'label' => 'Quelle'])
            //->add('tm', 'number', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehive_NumberWizard'))))
            //->add('hgv', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehive_NumberWizard'))))
            //->add('ddb', 'text', array('required' => $correction->getEdition()->getSort() == 0 ? false : true, 'attr' => array('wizard-url' => $this->generateUrl('PapyrillioBeehive_NumberWizard'))))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Correction::class,
        ]);
    }
}