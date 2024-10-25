<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    //    /**
    //     * @return Author[] Returns an array of Author objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Author
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    public function listAuthorByEmail()
    {
        return $this->createQueryBuilder('a')
                    ->leftJoin('a.books', 'b')
                    ->addSelect('b') // Charger les livres en même temps que les auteurs
                    ->orderBy('a.email', 'ASC')
                    ->getQuery()
                    ->getResult();
    }
    
public function searchBookByRef($ref)
{
    return $this->createQueryBuilder('b')
                ->where('b.ref = :ref')
                ->setParameter('ref', $ref)
                ->getQuery()
                ->getOneOrNullResult();
}
public function findAuthorsByBooksCount(int $minBooks, int $maxBooks): array
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.books', 'b')
        ->groupBy('a.id')
        ->having('COUNT(b.id) BETWEEN :minBooks AND :maxBooks')
        ->setParameter('minBooks', $minBooks)
        ->setParameter('maxBooks', $maxBooks)
        ->getQuery()
        ->getResult();
}
public function findAuthorsWithNoBooks(): array
{
    return $this->createQueryBuilder('a')
        ->leftJoin('a.books', 'b')
        ->groupBy('a.id')
        ->having('COUNT(b.id) = 0')
        ->getQuery()
        ->getResult();
}

}
