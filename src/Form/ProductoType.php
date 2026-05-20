<?php

namespace App\Form;

use App\Entity\Local;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'label' => 'Nombre del Producto',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('marca', TextType::class, [
                'label' => 'Marca',
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none']
            ])
            ->add('descripcion', TextareaType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none', 'rows' => 4]
            ])
            ->add('precio', \Symfony\Component\Form\Extension\Core\Type\NumberType::class, [
                'label' => 'Precio (€)',
                'scale' => 2,
                'attr' => [
                    'class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none',
                    'step' => '0.01'
                ]
            ])
            ->add('locales', EntityType::class, [
                'class' => Local::class,
                'choice_label' => 'nombre',
                'label' => '¿En qué locales se vende?',
                'multiple' => true,
                'expanded' => true,
                'attr' => ['class' => 'grid grid-cols-1 sm:grid-cols-2 gap-2 mt-2 p-4 bg-gray-50 rounded-xl border border-gray-200']
            ])
            ->add('stock', \Symfony\Component\Form\Extension\Core\Type\IntegerType::class, [
                'label' => 'Unidades en Stock',
                'required' => true,
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none', 'min' => 0]
            ])
            ->add('imagen', FileType::class, [
                'label' => 'Foto del Producto (Opcional)',
                'mapped' => false, // Importante: le decimos a Symfony que lo gestionaremos a mano
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Por favor, sube una imagen válida (JPG, PNG o WEBP)',
                    ])
                ],
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Producto::class,
        ]);
    }
}