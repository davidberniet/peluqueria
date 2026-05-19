<?php

namespace App\Form;

use App\Entity\Cita;
use App\Entity\Local;
use App\Entity\User;
use App\Repository\UserRepository;
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
        $local = $options['local'];

        $builder
            ->add('fechaInicio', DateTimeType::class, [
                'widget' => 'single_text',
                'label'  => 'Día y Hora de la cita',
                'attr'   => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none'],
            ])
            ->add('empleado', EntityType::class, [
                'class'         => User::class,
                'choice_label'  => 'nombre',
                'label'         => '¿Con quién te apetece?',
                'required'      => false,
                'placeholder'   => 'Sin preferencia (cualquiera)',
                // Filtramos solo usuarios con rol de empleado o admin y del local elegido
                'query_builder' => function (UserRepository $repo) use ($local) {
                    $qb = $repo->createQueryBuilder('u')
                        ->where("u.roles LIKE :rolEmpleado OR u.roles LIKE :rolAdmin");
                    
                    if ($local) {
                        $qb->andWhere('u.local = :local')
                           ->setParameter('local', $local);
                    }

                    return $qb->setParameter('rolEmpleado', '%ROLE_EMPLEADO%')
                              ->setParameter('rolAdmin', '%ROLE_ADMIN%')
                              ->orderBy('u.nombre', 'ASC');
                },
                'attr' => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none'],
            ])
            ->add('notas', TextareaType::class, [
                'required' => false,
                'label'    => 'Notas para el peluquero (opcional)',
                'attr'     => ['class' => 'w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-pink-500 outline-none', 'rows' => 3],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cita::class,
            'local'      => null,
        ]);
    }
}