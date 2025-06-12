<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Entity\Author;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

#[Route('api/authors', name: 'author_api_')]
final class AuthorController extends AbstractController
{
    #[Route('', name: 'authors', methods: ['GET'])]
    public function getAllAuthors(AuthorRepository $authorRepository, SerializerInterface $serializer): JsonResponse
    {
        $authorsList = $authorRepository->findAll();
        $jsonAuthorsList = $serializer->serialize($authorsList, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonAuthorsList, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'author_id', methods: ['GET'])]
    public function getAuthorById(?Author $author, SerializerInterface $serializer): JsonResponse
    {
        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBooks']);
        return new JsonResponse($jsonAuthor, Response::HTTP_OK, [], true);
    }
}
