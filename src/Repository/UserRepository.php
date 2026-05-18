<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Devuelve los usuarios que tienen rol de empleado o administrador.
     * Se usa para el selector de "¿Con quién te apetece?" en el formulario de reserva.
     *
     * @return User[]
     */
    public function findEmpleados(): array
    {
        // Doctrine almacena los roles como JSON. Filtramos con LIKE para ambos roles.
        return $this->createQueryBuilder('u')
            ->where("u.roles LIKE :rolEmpleado OR u.roles LIKE :rolAdmin")
            ->setParameter('rolEmpleado', '%ROLE_EMPLEADO%')
            ->setParameter('rolAdmin', '%ROLE_ADMIN%')
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
