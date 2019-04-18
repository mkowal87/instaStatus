<?php

namespace App\Repository;

use App\Entity\InstagramHashes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method InstagramHashes|null find($id, $lockMode = null, $lockVersion = null)
 * @method InstagramHashes|null findOneBy(array $criteria, array $orderBy = null)
 * @method InstagramHashes[]    findAll()
 * @method InstagramHashes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstagramHashesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, InstagramHashes::class);
    }

//    /**
//     * @return InstagramHashes[] Returns an array of InstagramHashes objects
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
    public function findOneBySomeField($value): ?InstagramHashes
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
