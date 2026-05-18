<?php

namespace App\Form;

use App\Entity\Local;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LocalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $inputClass = 'w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:outline-none focus:border-pink-400';

        $builder
            ->add('nombre', TextType::class, [
                'label'       => 'Nombre del local',
                'attr'        => ['class' => $inputClass, 'placeholder' => 'Ej: Venus Peluquería'],
                'constraints' => [new NotBlank(['message' => 'El nombre no puede estar vacío.'])],
            ])
            ->add('direccion', TextType::class, [
                'label'       => 'Dirección',
                'attr'        => ['class' => $inputClass, 'placeholder' => 'Ej: Calle Mayor, 12'],
                'constraints' => [new NotBlank(['message' => 'La dirección no puede estar vacía.'])],
            ])
            ->add('ciudad', TextType::class, [
                'label'       => 'Ciudad',
                'attr'        => ['class' => $inputClass, 'placeholder' => 'Ej: Madrid'],
                'constraints' => [new NotBlank(['message' => 'La ciudad no puede estar vacía.'])],
            ])
            ->add('telefono', TextType::class, [
                'label'    => 'Teléfono',
                'required' => false,
                'attr'     => ['class' => $inputClass, 'placeholder' => 'Ej: 953 123 456'],
            ])
            ->add('email', EmailType::class, [
                'label'    => 'Email de contacto',
                'required' => false,
                'attr'     => ['class' => $inputClass, 'placeholder' => 'info@venus.com'],
            ])
            ->add('activo', CheckboxType::class, [
                'label'    => 'Local activo (acepta reservas)',
                'required' => false,
                'attr'     => ['class' => 'w-5 h-5 rounded accent-pink-600'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Local::class,
        ]);
    }
}
