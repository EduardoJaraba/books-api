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

    /**
     * Searches for books based on title, author, or ISBN.
     *
     * @param string|null $title  The title to search for.
     * @param string|null $author The author to search for.
     * @param string|null $isbn   The ISBN to search for.
     * 
     * @return Book[] The list of books that match the search criteria.
     */
    public function searchBooks($title, $author, $isbn)
    {
        // Create a query builder to build the search query
        $queryBuilder = $this->createQueryBuilder('b');

        // Add conditions for title if provided
        if ($title) {
            $queryBuilder->andWhere('b.title LIKE :title')
                         ->setParameter('title', '%' . $title . '%');
        }

        // Add conditions for author if provided
        if ($author) {
            $queryBuilder->andWhere('b.author LIKE :author')
                         ->setParameter('author', '%' . $author . '%');
        }

        // Add conditions for ISBN if provided
        if ($isbn) {
            $queryBuilder->andWhere('b.isbn = :isbn')
                         ->setParameter('isbn', $isbn);
        }

        // Execute the query and return the result as an array of Book entities
        return $queryBuilder->getQuery()->getResult();
    }
}
