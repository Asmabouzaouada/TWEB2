<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;





/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    public function searchBookByRef($ref)
{
    return $this->createQueryBuilder('b')
                ->where('b.ref = :ref')
                ->setParameter('ref', $ref)
                ->getQuery()
                ->getOneOrNullResult();
}
public function booksListByAuthors()
{
    return $this->createQueryBuilder('b')
                ->join('b.Author', 'a')  // Utilise 'Author' si c'est ainsi que la relation est définie
                ->orderBy('a.username', 'ASC')
                ->getQuery()
                ->getResult();
}



public function updateCategoryFromSciFiToRomance(): int
{
    // Rechercher les livres avec la catégorie "Science-Fiction"
    return $this->createQueryBuilder('b')
                ->update(Book::class, 'b')
                ->set('b.category', ':newCategory')
                ->where('b.category = :oldCategory')
                ->setParameter('newCategory', 'Romance')
                ->setParameter('oldCategory', 'Science-Fiction')
                ->getQuery()
                ->execute(); // Cette méthode retourne le nombre de lignes affectées
}
public function countBooksByCategory(string $category): int
{
    return $this->createQueryBuilder('b')
        ->select('COUNT(b.id)')
        ->where('b.category = :category')
        ->setParameter('category', $category)
        ->getQuery()
        ->getSingleScalarResult();
}
public function findBooksBetweenDates(\DateTime $startDate, \DateTime $endDate)
{
    return $this->createQueryBuilder('b')
        ->where('b.publicationDate BETWEEN :startDate AND :endDate')
        ->setParameter('startDate', $startDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getResult();
}

}
