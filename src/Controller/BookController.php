<?php

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

final class BookController extends AbstractController
{
    #[Route('/api/books', name: 'book')]
    public function getAllBooks(BookRepository $bookRepository, SerializerInterface $serializer): JsonResponse
    {
       $bookList = $bookRepository->findAll();
       $jsonBooksList = $serializer->serialize($bookList, 'json');

       return new JsonResponse($jsonBooksList, Response::HTTP_OK, [], true);
    }
}
