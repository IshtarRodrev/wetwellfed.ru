<?php

namespace App\Form;

//use Doctrine\DBAL\Types\DateTimeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Validator\Constraints\IsTrue;
use App\Entity\Eater;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('birthdate', DateType::class, [
//                'scale'    => 2,
//                'attr'     => [
//                    'min'  => 10,
//                    'max'  => 600,
//                    'step' => 0.1,
//                ],
//                'widget' => 'choice',
                'widget' => 'single_text',
                'attr' => [
                    //'min' => (new \DateTime)->sub('') ('-100 years'))->format('dd-MM-yyyy'),
                    //'max' => (new \DateTime('-18 years'))->format('dd-MM-yyyy'),
                    'min' => (new \DateTime('-100 years'))->format('Y-m-d'),
                    'max' => (new \DateTime('-18 years'))->format('Y-m-d'),
                ],
//                'format' => 'dd-MM-yyyy',
                'required' => true,
                'html5' => true,
            ])
//            ->add('sex', SubmitType::class)
            ->add('sex', ChoiceType::class, [
                'choices'  => [
                    '♂ /'  => 0,
                    '♀' => 1,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('height', NumberType::class, [
                'scale'    => 2,
                'attr'     => [
                    'min'  => 50,
                    'max'  => 300,
                    'step' => 1,
                ],
                'required' => true,
                'html5' => true,
            ])
            ->add('weight', NumberType::class, [
                'scale'    => 2,
                'attr'     => [
                    'min'  => 10,
                    'max'  => 600,
                    'step' => 0.1,
                ],
                'required' => true,
                'html5' => true,
            ])
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->add('termsAgreement', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms to continue.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Eater::class,
        ]);
    }
}