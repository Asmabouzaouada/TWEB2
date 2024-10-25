<?php

namespace App\Controller;
use App\Entity\Author;
use App\Entity\Book;
use App\Form\BookType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }
  
   
      
    #[Route('/AfficheBook', name: 'app_AfficheBook')]
    public function Affiche(BookRepository $repository): Response
    {
        // Récupérer les livres publiés directement via le repository injecté
        $publishedBooks = $repository->findBy(['published' => true]);
        
        // Compter le nombre de livres publiés et non publiés
        $numPublishedBooks = count($publishedBooks);
        $numUnPublishedBooks = count($repository->findBy(['published' => false]));

        if ($numPublishedBooks > 0) {
            return $this->render('book/Affiche.html.twig', [
                'publishedBooks' => $publishedBooks,
                'numPublishedBooks' => $numPublishedBooks,
                'numUnPublishedBooks' => $numUnPublishedBooks
            ]);
        } else {
            // Afficher un message si aucun livre n'a été trouvé
            return $this->render('book/no_book_found.html.twig');
        }
    }

    #[Route('/addBook', name: 'app_AddBook')]
    public function Add(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->add('Ajouter', SubmitType::class);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $author = $book->getAuthor();
    
            // Incrémenter l'attribut "nb_books" de l'entité Author
            if ($author instanceof Author) {
                $author->setNbBooks($author->getNbBooks() + 1);
            }
    
            // Persister le livre et sauvegarder
            $em->persist($book);
            $em->flush();
    
            // Redirection vers la page d'affichage avec un message flash de succès
            $this->addFlash('success', 'Le livre a été ajouté avec succès !');
    
            return $this->redirectToRoute('app_AfficheBook');
        }
    
        return $this->render('book/Add.html.twig', [
            'f' => $form->createView(),
        ]);
    }
    
    #[Route('/editbook/{ref}', name: 'app_editBook')]
    public function edit(BookRepository $repository, $ref, Request $request, EntityManagerInterface $em): Response
    {
        $book = $repository->find($ref);
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé');
        }
    
        $form = $this->createForm(BookType::class, $book);
        $form->add('Edit', SubmitType::class);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
    
            // Message flash de succès
            $this->addFlash('success', 'Le livre a été modifié avec succès !');
    
            return $this->redirectToRoute("app_AfficheBook");
        }
    
        return $this->render('book/edit.html.twig', [
            'f' => $form->createView(),
        ]);
    }
    
    #[Route('/deletebook/{ref}', name: 'app_deleteBook')]
    public function delete($ref, BookRepository $repository, EntityManagerInterface $em): Response
    {
        $book = $repository->find($ref);
    
        if (!$book) {
            throw $this->createNotFoundException('Livre non trouvé');
        }
    
        $em->remove($book);
        $em->flush();
    
        return $this->redirectToRoute('app_AfficheBook');
    }
    
    #[Route('/ShowBook/{ref}', name: 'app_detailBook')]
public function showBook($ref, BookRepository $repository): Response
{
    $book = $repository->find($ref);
    if (!$book) {
        return $this->render('book/no_book_found.html.twig', [
            'message' => 'Livre non trouvé',
        ]);
    }

    return $this->render('book/show.html.twig', ['b' => $book]);
}




#[Route('/books/search', name: 'app_books_search')]
public function searchBookByRef(Request $request, BookRepository $bookRepository): Response
{
    $ref = $request->query->get('ref');
    $book = $bookRepository->searchBookByRef($ref);

    return $this->render('book/search_result.html.twig', [
        'book' => $book
    ]);
}
#[Route('/books/by-authors', name: 'app_books_by_authors')]
public function listBooksByAuthors(BookRepository $bookRepository): Response
{
    $books = $bookRepository->booksListByAuthors();
    return $this->render('book/list_by_authors.html.twig', [
        'books' => $books
    ]);
}

#[Route('/books/update-category', name: 'app_books_update_category')]
public function updateCategory(BookRepository $bookRepository): Response
{
    // Appeler la méthode pour modifier les catégories
    $affectedRows = $bookRepository->updateCategoryFromSciFiToRomance();

    // Ajouter un message flash de succès
    $this->addFlash('success', $affectedRows . ' livres ont été mis à jour de "Science-Fiction" à "Romance".');

    // Rediriger ou afficher un message de confirmation
    return $this->redirectToRoute('app_AfficheBook');
}

#[Route('/books/count-romance', name: 'books_count_romance')]
public function countRomanceBooks(BookRepository $bookRepository)
{
    $count = $bookRepository->countBooksByCategory('Romance');
    return $this->render('book/count_romance.html.twig', ['count' => $count]);
}

#[Route('/books/between-dates', name: 'books_between_dates')]
public function listBooksBetweenDates(BookRepository $bookRepository, Request $request)
{
    $startDate = new \DateTime('2014-01-01');
    $endDate = new \DateTime('2018-12-31');

    $books = $bookRepository->findBooksBetweenDates($startDate, $endDate);

    return $this->render('book/list_between_dates.html.twig', ['books' => $books]);
}

    
}

