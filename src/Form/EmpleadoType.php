<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmpleadoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre completo',
                'attr'  => [
                    'class'       => 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400',
                    'placeholder' => 'Ej: María García',
                ],
                'constraints' => [new NotBlank(['message' => 'El nombre no puede estar vacío.'])],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr'  => [
                    'class'       => 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400',
                    'placeholder' => 'empleado@venus.com',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'El email no puede estar vacío.']),
                    new Email(['message' => 'Introduce un email válido.']),
                ],
            ])
            ->add('telefono', TextType::class, [
                'label'    => 'Teléfono',
                'required' => false,
                'attr'     => [
                    'class'       => 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400',
                    'placeholder' => 'Ej: 612 345 678',
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label'    => 'Rol',
                'choices'  => [
                    'Empleado'       => 'ROLE_EMPLEADO',
                    'Administrador'  => 'ROLE_ADMIN',
                ],
                'expanded' => false,
                'multiple' => true,
                'attr'     => [
                    'class' => 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400',
                    'size'  => 2,
                ],
            ])
            ->add('local', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                'class'        => \App\Entity\Local::class,
                'choice_label' => 'nombre',
                'label'        => 'Local asignado',
                'required'     => false,
                'placeholder'  => 'Ningún local (Sede central / No asignado)',
                'attr'         => [
                    'class' => 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400',
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
