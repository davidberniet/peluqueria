<?php

namespace App\Form;

use App\Entity\Servicio;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServicioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del Servicio',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('categoria', TextType::class, [
                'label' => 'Categoría (Ej: Peluquería, Estética)',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('precio', MoneyType::class, [
                'label' => 'Precio (€)',
                'currency' => 'EUR',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('duration', IntegerType::class, [
                'label' => 'Duración (en minutos)',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('activo', CheckboxType::class, [
                'label' => '¿Visible para los clientes?',
                'required' => false,
                'attr' => ['class' => 'w-5 h-5 text-pink-600 rounded focus:ring-pink-500']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Servicio::class,
        ]);
    }
}