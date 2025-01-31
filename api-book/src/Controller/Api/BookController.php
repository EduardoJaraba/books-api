// src/Controller/Api/BookController.php

namespace App\Controller\Api;

use App\Message\BookFetchJob;
use App\Repository\BookRepository;
use App\Entity\Book;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    private MessageBusInterface $bus;
    private EntityManagerInterface $entityManager;

    public function __construct(MessageBusInterface $bus, EntityManagerInterface $entityManager)
    {
        $this->bus = $bus;
        $this->entityManager = $entityManager;
    }

    #[Route('/api/v1/books', methods: ['GET'])]
    public function searchBooks(Request $request, BookRepository $repo): JsonResponse
    {
        $isbn = $request->query->get('isbn');
        $author = $request->query->get('author');
        $title = $request->query->get('title');

        // Check if book already exists in DB before making external request
        if ($isbn) {
            $books = $repo->findBy(['isbn' => $isbn]);
        } elseif ($author) {
            $books = $repo->findBy(['author' => $author]);
        } elseif ($title) {
            $books = $repo->findBy(['title' => $title]);
        } else {
            return $this->json(['message' => 'No search parameters provided'], 400);
        }

        // If books found in DB, return them
        if (!empty($books)) {
            return $this->json($books);
        }

        // Dispatch job to fetch book info asynchronously if no match found in DB
        $searchQuery = $isbn ?? $author ?? $title;
        $this->bus->dispatch(new BookFetchJob($searchQuery));

        return $this->json(['message' => 'Fetching book info asynchronously...'], 202);
    }
}
