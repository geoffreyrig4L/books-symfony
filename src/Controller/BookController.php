<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Entity\Book;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/books', name: 'book_api_')]
final class BookController extends AbstractController
{
    #[Route('', name: 'books', methods : ['GET'])]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
       $bookList = $bookRepository->findAll();
       $jsonBooksList = $serializer->serialize($bookList, 'json', ['groups' => 'getBooks']);

       return new JsonResponse($jsonBooksList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'book_id', methods: ['GET'])]
    public function getBookById(?Book $book, SerializerInterface $serializer): JsonResponse
    {
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }
}
