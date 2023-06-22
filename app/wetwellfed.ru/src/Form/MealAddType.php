<?php

namespace App\Form;

use App\Entity\Meal;
use App\Entity\Eater;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MealAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('food', EntityType::class, [
                'label' => 'Наименование',
                'class' => 'App\Entity\Food',
                'query_builder' => function (\App\Repository\FoodRepository $er) use ($options) {
                    return $er->createQueryBuilder('f')
                        ->where('f.eater = :eater')
                        ->setParameter('eater', $options['eater'])
                        ->orderBy('f.name', 'ASC');
                },
                'choice_label' => function(?\App\Entity\Food $food) {
                    return $food ? $food->getSelectList() : '';
                },
                'required' => true,
                'expanded' => false,
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Количество',
                'scale'    => 2,
                'attr'     => [
                    'min'  => 0.1,
                    'max'  => 9999.99,
                    'step' => 0.05,
                ],
                'required' => true,
                'html5' => true,
            ])
            ->add('eatenAt', DateTimeType::class, [
                'label' => 'Количество',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Meal::class,
            'eater' => Eater::class,
        ]);
    }
}