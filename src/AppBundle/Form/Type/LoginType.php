<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Description of UserPrefs
 *
 * @author tpateffoz
 */
class LoginType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class,
                [
                    'label' => 'login.username',
                    'required' => true,
                    'attr' => ['placeholder' => 'login.username']
                ]
            )
            ->add('password', PasswordType::class,
                [
                    'label' => 'login.password',
                    'required' => true,
                    'attr' => ['placeholder' => 'login.password']
                ]
            )
            ->add('login', SubmitType::class, array('label' => 'login.button'));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Business\User\User',
            'translation_domain' => 'user'
        ));
    }
}