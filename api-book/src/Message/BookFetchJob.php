// src/Message/BookFetchJob.php
namespace App\Message;

class BookFetchJob
{
    private string $searchTerm;

    // Constructor to handle search term (ISBN, title, or author)
    public function __construct(string $searchTerm)
    {
        $this->searchTerm = $searchTerm;
    }

    public function getSearchTerm(): string
    {
        return $this->searchTerm;
    }
}


