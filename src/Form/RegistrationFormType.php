<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('nombre')
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce una contraseña',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Tu contraseña debe tener al menos {{ limit }} caracteres',
                        'max' => 20,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                        'message' => 'La contraseña debe incluir al menos una letra mayúscula, una minúscula y un número.',
                    ]),
                ],
            ])
            ->add('telefono', null, [
                'label' => 'Teléfono',
                'attr' => [
                    'placeholder' => 'Introduce tu número de teléfono',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Por favor, introduce un número de teléfono',
                    ]),
                    new Length([
                        'min' => 9,
                        'minMessage' => 'El número de teléfono debe tener al menos {{ limit }} caracteres',
                        'max' => 20,
                    ]),
                    new Regex([
                        'pattern' => '/^\+?[0-9]+$/',
                        'message' => 'El número de teléfono solo puede contener números y el símbolo + al inicio.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
