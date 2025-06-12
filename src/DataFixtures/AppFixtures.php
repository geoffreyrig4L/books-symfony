<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Author;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for($i=0; $i < 10; $i++) {
            $author = new Author();
            $author->setFirstName('Prenom ' . $i);
            $author->setLastName('Nom ' . $i);
            $manager->persist($author);
            $listAuthors[] = $author;
        }

        for($i=0; $i < 10; $i++) {
            $book = new Book();
            $book->setTitle('Book Title ' . $i);
            $book->setCoverText('Forth cover ' . $i);
            $book->setAuthor($listAuthors[array_rand($listAuthors)]);
            $manager->persist($book);
        }

        $manager->flush();
    }
}
