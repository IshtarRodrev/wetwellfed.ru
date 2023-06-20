<?php

namespace App\Form;

use App\Entity\Eater;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CategoryAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',  TextType::class, [
                'required' => true,
                'label' => 'Наименование'
            ])
            ->add('parent',  EntityType::class, [
                'label' => 'Наименование',
                'class' => 'App\Entity\Category',
                'query_builder' => function (\App\Repository\CategoryRepository $er) use ($options) {
                    return $er->createQueryBuilder('p')
                        ->where('p.eater = :eater')
                        ->setParameter('eater', $options['eater'])
                        ->andWhere('p.id != :exclude')
                        ->setParameter('exclude', $options['exclude'])
                        ->orderBy('p.name', 'ASC');
                },
                'choice_label' => function(?\App\Entity\Category $category) {
                    return $category ? $category->getName() : '';
                },
                'required' => false,
                'expanded' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'eater' => Eater::class,
            'exclude' => Category::class,
        ]);
    }
}
