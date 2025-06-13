<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use App\Entity\Book;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[Route('/api/books')]
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

    #[Route('/{id}', name: 'deleteBook', methods: ['DELETE'])]
    public function deleteBookById(?Book $book, EntityManagerInterface $em): JsonResponse
    {
        if (!$book) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($book);
        $em->flush(); //applique la modification dans la base de données

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
    #[Route('', name:"createBook", methods: ['POST'])]
    public function createBook(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AuthorRepository $authorRepository): JsonResponse 
    {

        $book = $serializer->deserialize($request->getContent(), Book::class, 'json');
        
        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        
        $book ->setAuthor($authorRepository->find($idAuthor)); //permet de lier le livre à un auteur
 
        $em->persist($book);
        $em->flush(); //applique la modification dans la base de données

        $jsonBook = $serializer->serialize($book, 'json', ['groups' => 'getBooks']);
        
        $location = $urlGenerator->generate('book_id', ['id' => $book->getId()], UrlGeneratorInterface::ABSOLUTE_URL); //permet de générer la route qui pourrait être utilisée pour récupérer des informations sur le livre ainsi créé.

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
   }

    #[Route('/{id}', name: 'updateBook', methods: ['PUT'])]
    public function updateBookById(Request $request, SerializerInterface $serializer, ?Book $currentBook, EntityManagerInterface $em, AuthorRepository $authorRepository): JsonResponse
    {
        if (!$currentBook) {
            return new JsonResponse(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $newBook = $serializer->deserialize($request->getContent(), Book::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentBook]); //le dernier paramètre est une clé, elle permet de mettre à jour un objet existant au lieu d'en créer un nouveau

        $content = $request->toArray();
        $idAuthor = $content['idAuthor'] ?? -1;
        $newBook ->setAuthor($authorRepository->find($idAuthor)); //permet de lier le livre à un auteur
 
        $em->persist($newBook);
        $em->flush(); //applique la modification dans la base de données

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
