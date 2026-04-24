<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
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
                    'autofocus'   => true,
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
            ->add('email', EmailType::class, [
                'label'       => 'Email address',
                'constraints' => [
                    new NotBlank(message: 'Please enter your email'),
                    new Email(message: 'Please enter a valid email'),
                ],
                'attr' => [
                    'class'       => 'w-full border border-gray-300
                                      rounded-lg px-4 py-2 text-sm
                                      focus:outline-none focus:ring-2
                                      focus:ring-indigo-400',
                    'placeholder' => 'john@example.com',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type'            => PasswordType::class,
                'mapped'          => false,
                'first_options'   => [
                    'label' => 'Password',
                    'attr'  => [
                        'class'       => 'w-full border border-gray-300
                                          rounded-lg px-4 py-2 text-sm
                                          focus:outline-none focus:ring-2
                                          focus:ring-indigo-400',
                        'placeholder' => 'At least 8 characters',
                        'autocomplete'=> 'new-password',
                    ],
                ],
                'second_options'  => [
                    'label' => 'Confirm password',
                    'attr'  => [
                        'class'       => 'w-full border border-gray-300
                                          rounded-lg px-4 py-2 text-sm
                                          focus:outline-none focus:ring-2
                                          focus:ring-indigo-400',
                        'placeholder' => 'Repeat your password',
                        'autocomplete'=> 'new-password',
                    ],
                ],
                'invalid_message' => 'The passwords do not match.',
                'constraints'     => [
                    new NotBlank(message: 'Please enter a password'),
                    new Length(
                        min: 8,
                        minMessage: 'Password must be at least
                                     {{ limit }} characters',
                        max: 4096
                    ),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped'      => false,
                'label'       => 'I agree to the terms of service',
                'constraints' => [
                    new IsTrue(
                        message: 'You must agree to the terms.'
                    ),
                ],
                'attr' => [
                    'class' => 'rounded border-gray-300 text-indigo-600
                                focus:ring-indigo-400',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }
}