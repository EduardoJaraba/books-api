namespace App\MessageHandler;

use App\Message\BookFetchJob;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface; // Import the logger

class BookFetchJobHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $entityManager;
    private MessageBusInterface $bus;
    private LoggerInterface $logger; // Inject the logger

    // Inject dependencies (including the logger)
    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $bus, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->logger = $logger;
    }

    // Handle the message and fetch book information
    public function __invoke(BookFetchJob $message)
    {
        $client = HttpClient::create(); // Create an HTTP client instance

        // Dynamically determine if we are searching by ISBN, title, or author
        $query = [];
        $searchTerm = $message->getSearchTerm();
        if (is_numeric($searchTerm)) {
            // If it's numeric (ISBN), use ISBN for the query
            $query['isbn'] = $searchTerm;
        } else {
            // Otherwise, use title or author (you can modify this if you have more types of search terms)
            $query['title'] = $searchTerm;
        }

        try {
            // Send request to the Open Library API
            $response = $client->request('GET', 'https://openlibrary.org/search.json', [
                'query' => $query
            ]);
        } catch (TransportExceptionInterface $e) {
            // Log any transport exceptions (e.g., network issues)
            $this->logger->error('Error while fetching books: ' . $e->getMessage());
            return;
        }

        // Handle rate limiting (429 response)
        if ($response->getStatusCode() === 429) {
            $retryAfter = $response->getHeaders(false)['retry-after'][0] ?? 60; // Get retry delay
            $this->bus->dispatch($message, [
                'delay' => $retryAfter * 1000  // Retry after the specified delay (in milliseconds)
            ]);
            return;
        }

        // Process the API response
        $data = $response->toArray();  // Convert response to an array
        if (empty($data['docs'])) {
            // If no books found, log it and return
            $this->logger->info('No books found for search term: ' . $searchTerm);
            return;
        }

        // Extract the first book from the response
        $bookData = $data['docs'][0];
        $book = new Book();
        $book->setTitle($bookData['title'] ?? 'Unknown')
             ->setIsbn($bookData['isbn'][0] ?? 'Unknown')
             ->setAuthor($bookData['author_name'][0] ?? 'Unknown');

        // Persist the book entity in the database
        $this->entityManager->persist($book);
        $this->entityManager->flush();

        // Log the successful book fetch
        $this->logger->info('Book saved: ' . $book->getTitle());
    }
}


