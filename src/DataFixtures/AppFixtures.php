<?php

namespace App\DataFixtures;

//use App\Entity\Comment;
use App\Entity\Comment;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use App\Entity\MainCategory;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    /**
    *Stockage des services utile à Symfony
     */
    private $slugger;
    private $encoder;

    /**
    * Récupérations auprès des service Symfony des services utile au fixtures
     */
    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $encoder)
    {
        // Service de slugs
        $this->slugger = $slugger;
        // Encodeur de mot de passe Hasher
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {
        // Instance du Faker (en français)
        $faker = Faker\Factory::create('fr_FR');

        // Création d'un compte admin
        $admin = new User();

        //Hydratation des données du compte Admin
        $admin
            ->setEmail('admin@a.a')
            ->setRegistrationDate( $faker->dateTimeBetween('-5 years', 'now'))
            ->setPseudonym( 'Géladalle')
            ->setRoles(["ROLE_ADMIN"])
            ->setBirthDate( $faker->dateTimeBetween('-60 years', '-13 years'))
            ->setPassword( $this->encoder->hashPassword($admin, 'Azerty8/'))
        ;

        // Persistance du compte admin
        $manager->persist($admin);

        // Création de 5 comptes utilisateur
        for($i = 0; $i < 5; $i++){

            // Création d'un compte utilisateur
            $user = new User();

            //Hydratation des données du compte utilisateur
            $user
                ->setEmail( $faker->email )
                ->setRegistrationDate( $faker->dateTimeBetween('-1 year', 'now'))
                ->setPseudonym( $faker->userName)
                ->setBirthDate( $faker->dateTimeBetween('-60 years', '-13 years'))
                ->setPassword( $this->encoder->hashPassword($user, 'Azerty8/'))
            ;

            // Persistance du compte utilisateur
            $manager->persist($user);

            // TODO: Stockage du compte pour créer des commentaires pour quand il seront crée
            //$users[] = $user;
        }

        // Ajout des Catégories

        $categories = [
            'Entrée',
            'Plat',
            'Dessert',
            'Cocktail'
        ];

        foreach( $categories as $clef => $category){

            // Création d'une catégorie
            $categoryToInsert = new MainCategory();

            //Hydratation du champs name
            $categoryToInsert
                ->setName($category)
            ;

            // Persistance de la catégorie
            $manager->persist($categoryToInsert);
        }

        // Ajout de 15 recettes
        for($i = 0; $i < 15; $i++) {

            // Création d'une recette
            $recipe = new Recipe();

            //Hydratation des recettes
            $recipe
                ->setAuthor($user)
                ->setContent('Le poulet du Bois de perle est un plat très répandu chez les colons de la Côte perlée et de la Baie du défi, mais sa popularité n’a jamais gagné le nord ni l’étranger. La population locale prétend que ce plat a été inventé par les nains de Talaneir. Compte tenu de l’étrange association d’ingrédients ...')
                ->setPublicationDate($faker->dateTimeBetween("-2year", "now"))
                ->setCost('Bon marché')
                ->setDifficulty('Difficile')
                ->setImage('/images/orange.jpg')
                ->setIngredients('- 1 cuillère à soupe d’huile d’olive
                    - 2 poitrines de poulet sans peau et désossées, coupées en deux (soit 4 morceaux)
                    - 2 épis de maïs, épluchés
                    - 1 cuillère à soupe et demie de sauce Southwest
                    - 12 cl de yaourt grec
                    - jus et zeste d’un citron vert
                    - sel et poivre')
                ->setMainCategory($categoryToInsert)
                ->setNbPeople('4 personnes')
                ->setSteps('Préchauffez le four à 200°C (thermostat 7). Badigeonnez l’huile sur le poulet et le maïs, et de placez-les ensemble sur une feuille de cuisson. Répartissez 1 cuillère à soupe de sauce sur le poulet. Fautes cuire 25 à 30 minutes, jusqu’à ce que le poulet soit cuit et le maïs brun doré. Retournez tout une ou deux fois pendant la cuisson. Mettez les grains de maïs dans un bol. Ajoutez le yaourt, 1/2 cuillère à soupe de sauce, le jus de citron et le zeste. Mélangez, et assaisonnez avec le sel et le poivre à votre goût. Couvrez le poulet avec ce mélange au maïs et servez.')
                ->setTitle('Poulet du Bois de perle' . $i)
                ->setPreparationTime(new \DateTime)
                ->setCookingTime(new \DateTime)
                ->setBreakTime(new \DateTime)
                ->setSlug('Poulet-du-Bois-de-perle' . $i)

            ;

            // Persistance des recettes
            $manager->persist($recipe);

            // Création de 0 à 10 commentaires aléatoire par article
//            $rand = rand(0,10);
//
//            for ($j = 0; $j < $rand; $j++ ){
//
//                $comment = new Comment;
//
//                $comment
//                    ->setRecipe($recipe)
//                    ->setAuthor($user)
//                    ->setContent("Voilà une bonne idée de recette, merci 🙂")
//                    ->setPublicationDate( $faker->dateTimeBetween("-2year", "now") )
//                ;
//
//                $manager->persist($comment);
//
//            }

        }

        // Sauvegarde dans la base de donné les nouvelles entités via leur manager général
        $manager->flush();
    }
}
