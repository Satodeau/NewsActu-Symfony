<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Categorie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'article',
                'constraints' =>[
                    new NotBlank([
                        'message' =>"Ce champ ne peut être vide"
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => "Votre titre est trop court.Le nombre de caractères minimal est de {{ limit }} caractères. ",
                        'maxMessage' => "Votre titre est trop long.Le nombre de caractères maximal est de {{ limit }} caractères. ",
                    ])
                ],

            ])
            
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
                'constraints' =>[
                    new NotBlank([
                        'message' =>"Ce champ ne peut être vide"
                    ]),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => "Votre sous-titre est trop court.Le nombre de caractères minimal est de {{ limit }} caractères. ",
                        'maxMessage' => "Votre sous-titre est trop long.Le nombre de caractères maximal est de {{ limit }} caractères. ",
                    ])
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => false, 
                'attr' => [
                    'placeholder' =>'Ici le contenu de l\'article'
                ],
                // Les contraintes de validation pour 'content' sont dans Article Entity (propriété $content)
            ])

            ->add('category', EntityType::class,[
                'class'=> Categorie::class,
                'choice_label' => 'name',
                'label' => 'Choississez une catégorie'
            ])

            ->add('photo', FileType::class, [
                'label' =>'Photo d\'illustration',
                // 'data_class'=> permet de paramétrer le type de classe dee données à null.
                // par défaut data_class = File

                'data_class'=> null,
                'attr' => [
                    'data-default-file' => $options['photo'],
                ],
                 'constraints' =>[
                     new Image([
                         'mimeTypes' => ["image/jpeg","image/png"],
                         'mimeTypesMessage' => "Les types de photos autorisés sont : .jpeg et .png",

                    ]),
                  ],

            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            //'allow_file_upload' => permet d'autoriser les upload de fichier dans le formulaire
            'allow_file_upload' => true,
            //'photo' => permet de récupérer la photo existante lors d'un update
            'photo' => null,
        ]);
    }
}
