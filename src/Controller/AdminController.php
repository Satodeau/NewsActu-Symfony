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

class AdminController extends AbstractController
{

    /**
     * @Route("/admin/tableau-de-bord", name="show_dashboard", methods={"GET"})
     */
    public function showDashboard(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('admin/show_dashboard.html.twig', [
            'articles' => $articles,
        ]);
    }







    /**
     * @Route("/admin/creer-un-article",name="create_article", methods={"GET|POST"})
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

            $this->addFlash('success' , 'Bravo,votre article est bien en ligne !');

            return $this->redirectToRoute('show_dashboard');

        } // END if ($form)

        return $this->render('admin/form/create_article.html.twig',[
            'form' => $form->createView()
        ]);
        
    }
}
