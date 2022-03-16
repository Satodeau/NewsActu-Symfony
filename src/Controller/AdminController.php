<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     * @Route("/tableau-de-bord", name="show_dashboard", methods={"GET"})
     */
    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();


        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
        ]);
    }







    /**
     * @Route("/creer-un-article",name="create_article", methods={"GET|POST"})
     */
    public function createArticle(Request $request,EntityManagerInterface $entityManager,SluggerInterface $slugger
    
    
    ): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleFormType::class, $article)->handleRequest($request);

        //Traitement du formulaire
        if($form->isSubmitted() && $form->isValid()) {


            // pour accéder à une valeur d'un input de $form,on fait :
                // $form->get('title')->getData

            // Setting des propriétés non mappées par le formulaire
            $article->setAlias($slugger->slug($article->getTitle() ) );
            $article->setCreatedAt(new DateTime());
            $article->setUpdatedAt(new DateTime());

            //Variabilisation du fichier 'photo' uploadé.
            $file = $form->get('photo')->getData();

            //if (isset($file) == true)
            // SI un fichier est uploadé (depuis le formulaire)
            if ($file) {
                // Maintenant il s'agit de reconstruire le nom du fichier pour le sécuriser.

                //1ère ETAPE : on déconstruit le nom du fichier et on variabilise.



                $extension = '.'. $file->guessExtension();
                  $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                


                // Assainissement du nom de fichier (du filename)
                 $safeFilename = $slugger->slug($originalFilename);
                //$safeFilename = $article->getAlias() ; //Pour mettre un alias sur la photo

                // 2ème ÉTAPE : on reconstruit le nom du fichier maintenant qu'il est safe.
                //uniqid() est une fonction native de PHP,elle permet d'ajouter une valeur numérique(id) eet auto générée
                $newFilename = $safeFilename . '_' . $extension;

                try {

                    // On  a configuré un paramètre 'uploads_dir' dans le fichier service.yaml
                    // On set le NOM de la photo, pas le CHEMIN

                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $article->setPhoto($newFilename);

                } catch (FileException $exception) {
 



                } // END catch()

            } // END if ($file)

            $entityManager->persist($article);
            $entityManager->flush();

            // ici on ajoute un meessage qu'on affichera en twig

            $this->addFlash('success' , 'Bravo,votre article '.$article->getTitle().' est bien en ligne !');

            return $this->redirectToRoute('show_dashboard');

        } // END if ($form)

        return $this->render('admin/form/form_article.html.twig',[
            'form' => $form->createView()
        ]);
        
    }

    /**
     * @Route("/modifier-un-article/{id}", name="update_article", methods={"GET|POST"})
     * L'action est exécutée 2x et accesible par deux méthods (GET|POST)
     */
    public function updateArticle(Article $article,Request $request, EntityManagerInterface $entityManager,SluggerInterface $slugger) : Response 
    {
        #Condition ternaire : $article->getPhoto() ?? '':
            # => est égale à isset:($article->getPhoto()) ?->getPhoto() : '';
        $originalPhoto = $article->getPhoto() ?? '';

        // 1er tour en méthode GET
        $form = $this->createForm(ArticleFormType::class, $article, [
            'photo' => $originalPhoto
        ])
        ->handleRequest($request);

        // 2ème TOUR de l'action en méthode POST
        if ($form->isSubmitted() && $form->isValid()) {

            $article->setAlias($slugger->slug($article->getTitle()));
            $article->setUpdatedAt(new DateTime());

            $file = $form->get('photo')->getData();

            if ($file) {
               

                $extension = '.'. $file->guessExtension();
                  $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                


                
                 $safeFilename = $slugger->slug($originalFilename);
               
                $newFilename = $safeFilename . '_' . $extension;

                try {

                  
                    $file->move($this->getParameter('uploads_dir'), $newFilename);

                    $article->setPhoto($newFilename);

                } catch (FileException $exception) {
                    # code à exécuter si une erreur est attrapée
                } // END catch()


            } else {
                $article->setPhoto($originalPhoto);




            } // END if ($file)


            $entityManager->persist($article);
            $entityManager->flush();

            $this->addFlash('success', "L'article ".$article->getTitle()." à bien été modifié");

            return $this->redirectToRoute("show_dashboard");



        } // END if ($form)

        // On retourne la vue pour la méthode GET
        return $this->render('admin/form/form_article.html.twig', [
            'form' => $form->createView(),
            'article'=>$article,
        ]);
    }

    /**
     * @Route("/archiver-un-article/{id}", name="soft_delete_article", methods={"GET"})
     */
    public function softDeleteArticle(Article $article, EntityManagerInterface $entityManager) : Response
    {
        $article->setDeletedAt(new DateTime());

        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success',"L'article '".$article->getTitle()."'a bien été archivé");

        return $this->redirectToRoute('show_dashboard');
    }

    /**
     * @Route("/supprimer-un-article/{id}", name="hard_delete_article", methods={"GET"})
     */
    public function hardDeleteArticle(Article $article, EntityManagerInterface $entityManager) : Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        $this->addFlash('success',"L'article '".$article->getTitle()."'a bien été supprimé de la base de données");

        return $this->redirectToRoute('show_dashboard');
    }

    /**
     * @Route("/restaurer-un-article/{id}", name="restore_article", methods={"GET"})
     */
    public function restoreArticle(Article $article, EntityManagerInterface $entityManager) : Response
    {
        $article->setDeletedAt();

        $entityManager->persist($article);
        $entityManager->flush();

        $this->addFlash('success',"L'article '".$article->getTitle()."'a bien été restauré");

        return $this->redirectToRoute('show_dashboard');
    }

} // END Class
