<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use AppBundle\Business\Exporter\ExportPrefs;

class ExportPrefsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sideForChoicesNotSet', ChoiceType::class, array(
                'choices'  => ExportPrefs::OPTIONS_SIDE_NOT_SET,
                'label' => 'export.sideForChoicesNotSet'
            ))
            ->add('sideForNewRecords', ChoiceType::class, array(
                'choices'  => ExportPrefs::OPTIONS_NEW_RECORDS,
                'label' => 'export.sideForNewRecords'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Business\Exporter\ExportPrefs',
            'translation_domain' => 'prefs'
        ));
    }

    public function getName()
    {
        return 'app_bundle_export_prefs_type';
    }
}
