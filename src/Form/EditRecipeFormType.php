<?php

namespace App\Form;

use App\Entity\MainCategory;
use App\Entity\Recipe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EditRecipeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de la recette',
                'attr' => [
                    'placeholder' => 'Quel est le nom de cette recette ?',
                ],
                'constraints' => [

                    // The length to respect
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères', // Error message if the title is smaller than 3 characters
                        'max' => 100,
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères', // Error message if the title is greater than 100 characters
                    ]),

                ],
            ])
            ->add('difficulty', ChoiceType::class, [
                'label' => 'Difficulté',
                'choices'  => [
                    'Facile' => 'Facile',
                    'Normale' => 'Normale',
                    'Difficile' => 'Difficile',
                ],
            ])
            ->add('cost', ChoiceType::class, [
                'label' => 'Coût',
                'choices'  => [
                    'Pas cher' => 'Pas cher',
                    'Bon marché' => 'Bon marché',
                    'Cher' => 'Cher',
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Note de l\'auteur',
                'attr' => [
                    'placeholder' => 'Décrivez la recette...',
                ],
                'constraints' => [

                    // The length to respect
                    new Length([
                        'min' => 10,
                        'minMessage' => 'La note doit contenir au moins {{ limit }} caractères', // Error message if the content is smaller than 10 characters
                        'max' => 1300,
                        'maxMessage' => 'La note doit contenir au maximum {{ limit }} caractères', // Error message if the content is greater than 1300 characters
                    ]),

                ],
            ])
            ->add('nbPeople', ChoiceType::class, [
                'label' => 'Nombre de personnes',
                'choices'  => [
                    'Une personne' => 'Une personne',
                    '2 personnes' => '2 personnes',
                    '3 personnes' => '3 personnes',
                    '4 personnes' => '4 personnes',
                    '5 personnes' => '5 personnes',
                    '6 personnes' => '6 personnes',
                    '7 personnes' => '7 personnes',
                    '8 personnes' => '8 personnes',
                    '9 personnes' => '9 personnes',
                    '10 personnes' => '10 personnes',
                    '11 personnes' => '11 personnes',
                    '12 personnes' => '12 personnes',
                    'Plus de 12 personnes' => 'Plus de 12 personnes',
                ],
            ])
            ->add('preparationTime', TimeType::class, [
                'label' => 'Temps de préparation (Heures/Minutes)',
            ])
            ->add('cookingTime', TimeType::class, [
                'label' => 'Temps de cuisson (Heures/Minutes)',
            ])
            ->add('breakTime', TimeType::class, [
                'label' => 'Temps de repos (Heures/Minutes)',
            ])
            ->add('ingredients', TextareaType::class, [
                'label' => 'Ingrédients',
                'purify_html' => true,
                'attr' => [
                    'placeholder' => 'Quels sont les ingrédients requis pour cette recette ?',
                ],
            ])
            ->add('steps', TextareaType::class, [
                'label' => 'Étapes',
                'purify_html' => true,
                'attr' => [
                    'placeholder' => 'Quelles sont les étapes de cette recette ?',
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Photos',
                'mapped' => false,
                'constraints' => [
                    new File([
                        // max length : 10Mo
                        'maxSize' => '10M',
                        // jpg and png only
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],

                        // Error message if the file type is not accepted
                        'mimeTypesMessage' => 'L\'image doit être du type JPG ou PNG seulement !',

                        // Error message if the file is too big
                        'maxSizeMessage' => 'Fichier trop volumineux ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }}{{ suffix }}',
                    ]),
                ]
            ])
            // premiere façon de stocker à la BDD
//            ->add('mainCategory', ChoiceType::class, [
//                'label' => 'Catégorie',
//                'choices' => [
//                    'entrée' => 'Entrée',
//                    'plat' => 'Plat',
//                    'dessert' => 'Dessert',
//                    'cocktail' => 'Cocktail',
//                ]
//            ])
            // Deuxieme façon de stocker les catégories en BDD
            ->add('mainCategory', EntityType::class, [
                'choice_label' => 'name',
                'class' => MainCategory::class,
                'label' => 'Catégorie',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Modifier la recette',
                'attr' => [
                    'class' => 'btn btn-outline-danger w-100',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
        ]);
    }
}
