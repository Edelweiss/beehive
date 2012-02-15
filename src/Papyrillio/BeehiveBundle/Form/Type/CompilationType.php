<?php

namespace Papyrillio\BeehiveBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CompilationType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('volume', 'number');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Papyrillio\BeehiveBundle\Entity\Compilation',
        );
    }

    public function getName()
    {
        return 'compilation';
    }
}