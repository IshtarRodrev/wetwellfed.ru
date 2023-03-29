<?php

namespace App\Form;

use App\Entity\Eater;
use App\Entity\Food;
use Doctrine\ORM\Mapping\Entity;
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
//                'choices' => $category->getUsers(),
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
//            ->add('calories', CheckboxType::class, [
//                'mapped' => false,
//                'constraints' => [
//                    new IsTrue([
//                        'message' => 'You should agree to our terms.',
//                    ]),
//                ],
//            ])
            ->add('amountType', ChoiceType::class, [
                'choices'  => [
                    'Pack'  => Food::AMOUNT_TYPE_PACK,
                    'Grams' => Food::AMOUNT_TYPE_GRAM,
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('weight', IntegerType::class, [
                //
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
//            'data_class' => Food::class,
            'eater' => Eater::class,
        ]);
    }

    public function validateWeight($value, ExecutionContextInterface $context)
    {
        $form = $context->getRoot();
        $food = $form->getData();

        if ($food->getAmountType() === Food::AMOUNT_TYPE_PACK && $food->getWeight() < 1) // GRAM=1 PACK=0
        {
            $context
                ->buildViolation('Ukazhite ves pachki')
                ->atPath('weight')
                ->addViolation();
        }
    }
}
