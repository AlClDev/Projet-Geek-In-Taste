<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Form\AddCommentFormType;
use App\Entity\Comment;
use App\Form\CreateAndEditRecipeFormType;
use App\Form\EditRecipeFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RecipeController extends AbstractController
{
    /**
     * Recipe Creation Page Controller
     *
     * Open to logged users only
     */
    #[Route('/creer-une-recette/', name: 'app_recipe')]
    #[IsGranted('ROLE_USER')]
    public function recipe(Request $request, ManagerRegistry $doctrine, SluggerInterface $slugger): Response
    {

        $recipe = new Recipe();

        $recipe_form = $this->createForm(CreateAndEditRecipeFormType::class, $recipe);

        $recipe_form->handleRequest($request);

        if ($recipe_form->isSubmitted() && $recipe_form->isValid()) {

            $photo = $recipe_form->get('image')->getData();

            $newFileName = md5(time() . rand() . uniqid()) . '.' . $photo->guessExtension();

            $recipe
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setSlug($slugger->slug($recipe->getTitle())->lower())
                ->setImage($newFileName)
            ;

            $photo->move(
                $this->getParameter('app.recipe.photo.directory'),
                $newFileName
            );

            $em = $doctrine->getManager();

            $em->persist($recipe);

            $em->flush();

            $this->addFlash('success', 'Recette publiée avec succès !');

            return $this->redirectToRoute('recipe_view', [
                'id' => $recipe->getId(),
                'slug' => $recipe->getSlug(),
            ]);
        }

        dump($recipe);

        return $this->render('recipe/recipe.html.twig', [
            'recipe_form' => $recipe_form->createView(),
        ]);
    }


    #[Route('/recettes/', name: 'recipe_list')]
    public function recipeList(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        $requestedPage = $request->query->getInt('page', 1);

        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Recipe a ORDER BY a.publicationDate DESC');

        $recipes = $paginator->paginate(
            $query,
            $requestedPage,
            9,
        );

        return $this->render('recipe/recipe_list.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    /**
     * Controleur de la liste de recette par catégories
     */
    #[Route('/recettes/categories/{id}/', name: 'recipe_list_categories')]
    public function recipeListCategories(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator, $id): Response{

        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em ->getRepository(Recipe::class) ->createQueryBuilder('r') ->join('r.mainCategory', 'mc') ->where('mc.id = :category_id') ->setParameter('category_id',$id) ->getQuery() ->getResult();


        $recipes = $paginator->paginate(
            $query,
            $requestedPage,
            10,
        );

        return $this->render('recipe/recipe_list.html.twig', [
            'recipes' => $recipes,
        ]);

    }


    /**
     * Controller for recipe view page
     * (with id and slug)
     */
    #[Route('/recette/{id}/{slug}/', name: 'recipe_view')]
    #[ParamConverter('recette', options: ['mapping' => ['id' => 'id', 'slug' => 'slug']])]
    public function viewRecipe(Recipe $recipe, Request $request, ManagerRegistry $doctrine): Response
    {

        // For skip comment form if user is disconnected
        if (!$this->getUser()) {
            return $this->render('recipe/recipe_view.html.twig', [
                'recipe' => $recipe,
            ]);
        }

        // Creation of an empty comment
        $comment = new Comment();

        $commentForm = $this->createForm(AddCommentFormType::class, $comment);

        $commentForm->handleRequest($request);

        // Without errors
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            // Hydratation
            $comment
                ->setPublicationDate(new \DateTime())
                ->setAuthor($this->getUser())
                ->setRecipe($recipe);;

           try{
               // Data base saving
               $em = $doctrine->getManager();
               $em->persist($comment);
               $em->flush();

               // Flash success message
               $this->addFlash('success', 'Commentaire publié avec succès !');

           }catch (\Exception $exception){

               // Flash error message
               $this->addFlash('error', 'Désolé, un problème est survenu !');

           }

            // Delete form and comment variables
            unset($comment);
            unset($commentForm);

            // New empty comment form
            $comment = new Comment();
            $commentForm = $this->createForm(AddCommentFormType::class, $comment);
        }

        return $this->render('recipe/recipe_view.html.twig', [
            'recipe' => $recipe,
            'commentForm' => $commentForm->createView(),
        ]);
    }

    /**
     * Controller for admin page to delete comment
     * (with id)
     *
     * Only admin access
     */
    #[Route('/commentaire/suppression/{id}/', name: 'comment_delete', priority: 10)]
    #[IsGranted('ROLE_ADMIN')]
    public function commentDelete(Comment $comment, ManagerRegistry $doctrine, Request $request): Response
    {

        // If token csrf in url is wrong
        if (!$this->isCsrfTokenValid('comment_delete_' . $comment->getId(), $request->query->get("csrf_token"))) {

            // Flash success message
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer !');
        } else {

            // Delete comment from data base
            $em = $doctrine->getManager();
            $em->remove($comment);
            $em->flush();

            // Flash errors message
            $this->addFlash('success', 'Commentaire supprimé avec succès !');
        }

        // Redirection
        return $this->redirectToRoute('recipe_view', [
            'slug' => $comment->getRecipe()->getSlug(),
            'id' => $comment->getRecipe()->getId(),
        ]);
    }


    #[Route('/recette/suppression/{id}/', name: 'recipe_delete', priority: 10)]
    public function publicationDelete(Recipe $recipe, ManagerRegistry $doctrine, Request $request): Response
    {

        $csrfToken = $request->query->get('csrf_token', '');

        if (!$this->isCsrfTokenValid('recipe_delete' . $recipe->getId(), $csrfToken)) {

            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer !');
        } else {

            if ($this->isGranted('ROLE_ADMIN') || $recipe->getAuthor() == $this->getUser()) {

                $em = $doctrine->getManager();
                $em->remove($recipe);
                $em->flush();

                $this->addFlash('success', 'Recette supprimée avec succès !');
            } else {
                $this->addFlash('error', 'Vous n\'avez pas la permission pour cette action !');
            }
        }

        return $this->redirectToRoute('recipe_list');
    }


    #[Route('/recette/modifier/{id}/', name: 'recipe_edit', priority: 10)]
    #[IsGranted('ROLE_USER')]
    public function publicationEdit(Recipe $recipe, ManagerRegistry $doctrine, Request $request, SluggerInterface $slugger): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {

            if($recipe->getAuthor() != $this->getUser()){
                throw new AccessDeniedException();
            }
        }

        $form = $this->createForm(EditRecipeFormType::class, $recipe);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            $recipe->setSlug($slugger->slug($recipe->getTitle())->lower());


            $photo = $form->get('image')->getData();

            if(!empty($photo)){

                if(
                    $recipe->getImage() != null &&
                    file_exists( $this->getParameter('app.recipe.photo.directory') . $recipe->getImage())
                ){

                    // Suppression de l'ancienne photo
                    unlink( $this->getParameter('app.recipe.photo.directory') . $recipe->getImage() );
                }

                do {

                    $newFileName = md5(random_bytes(100)) . '.' . $photo->guessExtension();
                } while (file_exists($this->getParameter('app.recipe.photo.directory') . $newFileName));

                $photo->move(
                    $this->getParameter('app.recipe.photo.directory'),
                    $newFileName
                );

                $recipe->setImage($newFileName);

            }



            $em = $doctrine->getManager();
            $em->flush();

            $this->addFlash('success', 'Recette modifiée avec succès !');

            return $this->redirectToRoute('recipe_view', [
                'id' => $recipe->getId(),
                'slug' => $recipe->getSlug(),
            ]);

        }

        return $this->render('recipe/recipe_edit.html.twig', [
            'recipe_form' => $form->createView(),
        ]);
    }

    /**
     * Controller de la page affichant les résultats des recherches faites par le formulaire de recherche
     */
    #[Route('/recherche/', name: 'recipe_search')]
    public function search(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération de $_GET['page'], 1 si elle n'existe pas
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le nombre est positif

        if($requestedPage < 1 ){
            throw new NotFoundHttpException();
        }

        // On récupère la recherche de l'utilisateur depuis l'URL ( $_GET['search'] )
        $search = $request->query->get('search', '');

        $em = $doctrine->getManager();

        //Création de la requête de recherche
        $query = $em
            ->createQuery('SELECT a FROM App\Entity\Recipe a WHERE a.title LIKE :search OR a.content LIKE :search ORDER BY a.publicationDate DESC')
            ->setParameters([
                'search' => '%' . $search . '%'
            ]);

        $recipes = $paginator->paginate(
            $query,     // Requête créée juste avant
            $requestedPage,     // Page qu'on souhaite voir
            10,     // Nombre d'article à afficher par page
        );

        return $this->render('recipe/list_search.html.twig', [
            'recipes' => $recipes,
        ]);
    }
}
