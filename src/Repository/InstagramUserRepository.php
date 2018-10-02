<?php

namespace App\Repository;

use App\Entity\InstagramUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InstagramUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstagramUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstagramUser[]    findAll()
 * @method InstagramUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstagramUserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InstagramUser::class);
    }

//    /**
//     * @return InstagramUser[] Returns an array of InstagramUser objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InstagramUser
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
