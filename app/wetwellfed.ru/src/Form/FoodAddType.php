<?php

namespace App\Form;

use App\Entity\Eater;
use App\Entity\Food;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

class FoodAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',  TextType::class, [
                'required' => true,
                'label' => 'Наименование'
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'placeholder' => '-- choose one --',
                'query_builder' => function (EntityRepository $er) use ($options) {
                    return $er->createQueryBuilder('c')
                        ->where('c.eater = :eater')
                        ->setParameter('eater', $options['eater'])
                        ->orderBy('c.name', 'ASC')
                        ;
                },
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Category',
                'attr' => ['class' => 'toggle'] // This is HTML class
                ])
            ->add('calories', IntegerType::class, [
                'required' => true,
                'label' => 'cal / 100g'
            ])
            ->add('amountType', ChoiceType::class, [
                'choices'  => [
                    'Pack'  => Food::AMOUNT_TYPE_PACK,
                    'Grams' => Food::AMOUNT_TYPE_GRAM,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('weight', IntegerType::class, [
                'required'   => false,
                'label' => 'Вес одной единицы',
                'attr' => [
                    'min' => '1',
                    'step' => '1',
                ],
                'empty_data' => '0',
                'constraints' => [
                    new Callback(array($this, 'validateWeight')),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'eater' => Eater::class,
        ]);
    }

    public function validateWeight($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $food = $form->getData();

        if ($food->getAmountType() === Food::AMOUNT_TYPE_PACK && $food->getWeight() < 1) // Gram=1 Pack=0
        {
            $context
                ->buildViolation('Ukazhite ves pachki')
                ->atPath('weight')
                ->addViolation();
        }
    }
}
