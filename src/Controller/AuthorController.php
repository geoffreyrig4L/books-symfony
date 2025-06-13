<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use App\Entity\Author;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Utils\ValidationErrorHandler;

#[Route('api/authors')]
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

    #[Route('/{id}', name: 'deleteAuthor', methods: ['DELETE'])]
    public function deletaAuthorById(?Author $author, EntityManagerInterface $em) : JsonResponse
    {
        if(!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($author);
        $em->flush(); //applies the change to the database
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name : "updateAuthor", methods: ['PUT'])]
    public function updateAuthorById(?Author $currentAuthor, SerializerInterface $serializer, Request $request, EntityManagerInterface $em) : JsonResponse {
        if(!$currentAuthor) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $newAuthor = $serializer->deserialize($request->getContent(), Author::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAuthor]);

        $em->persist($newAuthor);
        $em->flush(); //applique la modification dans la base de données

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('', name: "createAuthor", methods: ['POST'])]
    public function createAuthor(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator) : JsonResponse
    {
        $author = $serializer->deserialize($request->getContent(), Author::class, 'json');

        $errors = $validator->validate($author); 
        ValidationErrorHandler::handle($errors);

        $em->persist($author);
        $em->flush(); //applies the change to the database

        $jsonAuthor = $serializer->serialize($author, 'json', ['groups' => 'getBooks']);

        $location = $urlGenerator->generate('author_id', ['id' => $author->getId()], UrlGeneratorInterface::ABSOLUTE_URL); //le dernier paramètre signifie 'Génére-moi une URL complète avec le nom de domaine'

        return new JsonResponse($jsonAuthor, Response::HTTP_CREATED, ['Location' => $location], true); // le dernier paramètre signifie  'Le contenu que je te donne ($jsonAuthor) est déjà du JSON, donc ne le réencode pas'
    }
}
