<?php

namespace App\Controller;
use App\Form\AuthorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AuthorRepository;
use App\Entity\Author;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }
    #[Route('/showAuthor/{name}', name: 'app_show_author')]
    public function showAuthor(string $name): Response
    {
        return $this->render('author/show.html.twig', [
            'n' => $name,
        ]);
    }
    #[Route('/showlist', name: 'app_show_list')]
    public function list()
    {
        $authors = array(
            array('id' => 1, 'picture' => '/images/Victor-Hugo.jpg', 'username' => 'Victor Hugo', 'email' => 'victor.hugo@gmail.com', 'nb_books' => 100),
            array('id' => 2, 'picture' => '/images/william-shakespeare.jpg', 'username' => 'William Shakespeare', 'email' => 'william.shakespeare@gmail.com', 'nb_books' => 200),
            array('id' => 3, 'picture' => '/images/Taha_Hussein.jpg', 'username' => 'Taha Hussein', 'email' => 'taha.hussein@gmail.com', 'nb_books' => 300),
        );
    
        return $this->render('author/list.html.twig', ['authors' => $authors]);
    }
    #[Route('/Affiche', name: 'app_Affiche')]
    public function Affiche(AuthorRepository $repository ):response
    {
        $author=$repository->findAll(); 
        return $this->render('/author/Affiche.html.twig', ['authors' => $author]);

    }
    #[Route('/Add', name: 'app_AddAuthor')]
   
    public function addAuthor(EntityManagerInterface $entityManager): Response
    {
        // Créer un nouvel objet Author avec des données statiques
        $author = new Author();
        $author->setUsername('JohnDoe');
        $author->setEmail('johndoe@example.com');

        // Utiliser l'EntityManager pour enregistrer l'auteur dans la base de données
        $entityManager->persist($author);
        $entityManager->flush();

        // Retourner une réponse pour indiquer que l'auteur a été ajouté
        return new Response('Auteur ajouté avec succès : ' . $author->getUsername());
    }

  #[Route('/addAuthor2', name: 'app_add_author', methods: ['GET', 'POST'])]
    public function addAuthor2(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si la requête est de type POST (le formulaire a été soumis)
        if ($request->isMethod('POST')) {
            // Récupérer les données du formulaire
            $username = $request->request->get('username');
            $email = $request->request->get('email');

            // Créer un nouvel objet Author et définir les valeurs
            $author = new Author();
            $author->setUsername($username);
            $author->setEmail($email);

            // Sauvegarder l'auteur dans la base de données
            $entityManager->persist($author);
            $entityManager->flush();

            // Rediriger ou retourner un message de succès
            return new Response('Auteur ajouté avec succès : ' . $author->getUsername());
        }

        // Afficher le formulaire si la requête est de type GET
        return $this->render('author/addAuthor.html.twig');
    }

   
    #[Route('/addAuthor', name: 'app_Add', methods: ['GET', 'POST'])]
public function Add(Request $request, EntityManagerInterface $entityManager)
{
    $author = new Author();
    $form = $this->createForm(AuthorType::class, $author);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Utilisation de l'EntityManager injecté
        $entityManager->persist($author);
        $entityManager->flush();

        return $this->redirectToRoute('app_Affiche');
    }

    return $this->render('author/Add.html.twig', ['f' => $form->createView()]);
}

#[Route('/edit/{id}', name: 'app_edit')]
public function edit(AuthorRepository $repository, $id, Request $request, EntityManagerInterface $entityManager)
{
    $author = $repository->find($id);
    if (!$author) {
        throw $this->createNotFoundException('Author not found');
    }

    $form = $this->createForm(AuthorType::class, $author);
    $form->add('Edit', SubmitType::class);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Utilisation de l'EntityManager injecté
        $entityManager->flush();

        return $this->redirectToRoute("app_Affiche");
    }

    return $this->render('author/edit.html.twig', [
        'f' => $form->createView(),
    ]);
}


    // Méthode de suppression d'un auteur
    #[Route('/author/delete/{id}', name: 'app_delete_author', methods: ['POST'])]
    public function deleteAuthor(EntityManagerInterface $entityManager, Author $author): Response
    {
        if ($author)
       {
        $entityManager->remove($author);
        $entityManager->flush();
       }
       

        return $this->redirectToRoute('app_Affiche');
    }
    #[Route('/authors/by-email', name: 'app_authors_by_email')]
    public function listAuthorsByEmail(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->listAuthorByEmail();
        return $this->render('author/list_by_email.html.twig', [
            'authors' => $authors
        ]);
    }
    
    #[Route('/authors/search', name: 'authors_search_by_books', methods: ['GET', 'POST'])]
    public function searchAuthorsByBooksCount(Request $request, AuthorRepository $authorRepository): Response
    {
        $minBooks = $request->query->get('min', 0); // Valeur par défaut si non définie
        $maxBooks = $request->query->get('max', PHP_INT_MAX); // Valeur maximale par défaut
    
        // Récupérer les auteurs dont le nombre de livres est compris entre min et max
        $authors = $authorRepository->findAuthorsByBooksCount($minBooks, $maxBooks);
    
        return $this->render('author/search_by_books.html.twig', [
            'authors' => $authors,
            'minBooks' => $minBooks,
            'maxBooks' => $maxBooks,
        ]);
    }
    #[Route('/authors/delete-no-books', name: 'authors_delete_no_books', methods: ['GET', 'POST'])]
    public function deleteAuthorsWithNoBooks(AuthorRepository $authorRepository, EntityManagerInterface $entityManager): Response
    {
        $authors = $authorRepository->findAuthorsWithNoBooks();
    
        foreach ($authors as $author) {
            $entityManager->remove($author);
        }
    
        $entityManager->flush();
    
        $this->addFlash('success', 'Les auteurs sans livres ont été supprimés.');
    
        return $this->redirectToRoute('authors_search_by_books');
    }
    

}
