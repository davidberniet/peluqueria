<?php

namespace App\Form;

use App\Entity\Cita;
use App\Entity\Local;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CitaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fechaInicio', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Día y Hora de la cita',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('local', EntityType::class, [
                'class' => Local::class,
                'choice_label' => 'nombre', // Asegúrate de que tu entidad Local tiene un campo nombre
                'label' => '¿En qué local?',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('empleado', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nombre',
                'label' => '¿Con quién te apetece?',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('notas', TextareaType::class, [
                'required' => false,
                'label' => 'Notas para el peluquero (opcional)',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none', 'rows' => 3]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cita::class,
        ]);
    }
}