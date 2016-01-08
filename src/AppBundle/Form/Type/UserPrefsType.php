<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of UserPrefs
 *
 * @author tpateffoz
 */
class UserPrefsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dwcDelimiter')
            ->add('dwcEnclosure')
            ->add('dwcLineBreak')
            ->add('csvDelimiter')
            ->add('csvEnclosure')
            ->add('csvLineBreak')
            ->add('save', SubmitType::class)
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Business\User\Prefs',
        ));
    }
}