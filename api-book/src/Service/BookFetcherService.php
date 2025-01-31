<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BookFetcherService
{
    private $httpClient;
    
    // Injecting the HttpClientInterface into the service
    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    // Function to fetch books from the Open Library API
    public function fetchBooksByTitle(string $title): array
    {
        // Making the HTTP request to the Open Library API
        $response = $this->httpClient->request('GET', 'https://openlibrary.org/search.json', [
            'query' => ['title' => $title]
        ]);
        
        // Fetching the data from the response
        $data = $response->toArray();

        // Returning the results in a structured format
        return $data['docs'] ?? [];
    }

    public function fetchBooksByIsbn(string $isbn): array
    {
        // Making the HTTP request to the Open Library API
        $response = $this->httpClient->request('GET', 'https://openlibrary.org/api/books', [
            'query' => ['bibkeys' => 'ISBN:' . $isbn, 'format' => 'json', 'jscmd' => 'data']
        ]);

        // Fetching the data from the response
        $data = $response->toArray();

        // Returning the results in a structured format
        return $data ? $data : [];
    }
}
