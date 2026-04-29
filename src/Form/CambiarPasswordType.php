<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class CambiarPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('passwordActual', PasswordType::class, [
                'label' => 'Contraseña actual',
                'constraints' => [new NotBlank()],
            ])
            ->add('nuevaPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Nueva contraseña'],
                'second_options' => ['label' => 'Repetir nueva contraseña'],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6, 'max' => 255]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}