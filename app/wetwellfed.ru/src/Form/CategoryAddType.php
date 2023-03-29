<?php

namespace App\Form;

use App\Entity\Eater;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Query\Expr\Select;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\EntityRepository;
use App\Repository\CategoryRepository;

class CategoryAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        var_dump($options['eater']);
//        exit();
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
//            'data_class' => Food::class,
            'eater' => Eater::class,
//            'category' => Category::class,
            'exclude' => Category::class,
        ]);
    }
}
