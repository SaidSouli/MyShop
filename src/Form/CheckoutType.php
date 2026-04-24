<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('firstName', TextType::class, [
                'label'       => 'First name',
                'constraints' => [
                    new NotBlank(message: 'Please enter your first name'),
                    new Length(max: 100),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => 'John',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label'       => 'Last name',
                'constraints' => [
                    new NotBlank(message: 'Please enter your last name'),
                    new Length(max: 100),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => 'Doe',
                ],
            ])
            ->add('address', TextType::class, [
                'label'       => 'Street address',
                'constraints' => [
                    new NotBlank(message: 'Please enter your address'),
                    new Length(max: 255),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => '123 Main Street',
                ],
            ])
            ->add('city', TextType::class, [
                'label'       => 'City',
                'constraints' => [
                    new NotBlank(message: 'Please enter your city'),
                    new Length(max: 100),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => 'Tunis',
                ],
            ])
            ->add('postcode', TextType::class, [
                'label'       => 'Postcode',
                'constraints' => [
                    new NotBlank(message: 'Please enter your postcode'),
                    new Length(max: 20),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => '1000',
                ],
            ])
            ->add('country', TextType::class, [
                'label'       => 'Country',
                'constraints' => [
                    new NotBlank(message: 'Please enter your country'),
                    new Length(max: 100),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => 'Tunisia',
                ],
            ])
            ->add('note', TextareaType::class, [
                'label'    => 'Order note (optional)',
                'required' => false,
                'attr'     => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'rows'        => 3,
                    'placeholder' => 'Any special instructions?',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}