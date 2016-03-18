<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Business\User\Prefs;

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
            ->add('dwcDelimiter', TextType::class, array('label' => 'prefs.dwcDelimiter'))
            ->add('dwcEnclosure', TextType::class, array('label' => 'prefs.dwcEnclosure', 'required' => false))
            ->add('dwcLineBreak', TextType::class, array('label' => 'prefs.dwcLineBreak'))
            ->add('dwcDateFormat', ChoiceType::class, array(
                'choices'  => Prefs::DATE_DISPLAY_FORMAT,
                // *this line is important*
                'choices_as_values' => true,
                'label' => 'prefs.dwcDateFormat'
            ))
            ->add('csvDelimiter', TextType::class, array('label' => 'prefs.csvDelimiter'))
            ->add('csvEnclosure', TextType::class, array('label' => 'prefs.csvEnclosure', 'required' => false))
            ->add('csvLineBreak', TextType::class, array('label' => 'prefs.csvLineBreak'))
            ->add('csvDateFormat', ChoiceType::class, array(
                'choices'  => Prefs::DATE_DISPLAY_FORMAT,
                // *this line is important*
                'choices_as_values' => true,
                'label' => 'prefs.csvDateFormat'
            ))
            ->add('save', SubmitType::class, array('label' => 'prefs.save'))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Business\User\Prefs',
            'translation_domain' => 'prefs'
        ));
    }
}
