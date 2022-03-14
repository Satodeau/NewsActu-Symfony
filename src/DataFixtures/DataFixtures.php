<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class DataFixtures extends Fixture
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    
    # Cette fonction load() sera exécutée en ligne de commande, avec : php bin/console doctrine:fixture:load --append
    # => le drapeau --append permet de ne pas purger la BDD.

    public function load(ObjectManager $manager): void
    {
        //déclaration d'unee varriable de type array,avec le nom des différentes catégories de NewsActu.
        $categories = [
            'Politique',
            'Société',
            'People',
            'Économie',
            'Espace',
            'Sciences',
            'Mode',
            'Informatique',
            'Écologie',
            'Cinéma',
            'Hi Tech',
        ];

        //La boucle foreach() est optimisée pour les array.
            //La syntaxe complète à l'intérieur des parenthèses est : ($key =>$values)
        foreach($categories as $cat){

            //Instanciation d'un objet Categorie
            $categorie = new Categorie();


            //Appel des setteers de notre Objet
            $categorie->setName($cat);
            $categorie->setAlias($this->slugger->slug($cat));
            $categorie->setCreatedAt(new DateTime());
            $categorie->setUpdatedAt(new DateTime());

            $manager->persist($categorie);
        }

        $manager->flush();
    }
}
