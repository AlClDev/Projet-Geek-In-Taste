<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordTypeFormType;
use App\Form\EditProfilFormType;
use App\Form\EditProfilPhotoTypeFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;



#[Route('', name: 'main_')]
class MainController extends AbstractController
{
    /**
     * contrôleur de la page d'accueil
     */
    #[Route('/', name: 'home')]
    public function home(): Response
    {

        return $this->render('main/home.html.twig');
    }



    /**
     * Controller to profil page
     */
    #[Route('/mon-profil/', name: 'profils')]
    #[IsGranted("ROLE_USER")]
    public function profils(): Response
    {
        return $this->render('main/profils.html.twig');
    }



    /**
     * Controller to profil parameter
     */
    #[Route('/mon-profil/options/', name: 'profils_parameter')]
    #[IsGranted("ROLE_USER")]
    public function profilsParameter(Request $request, EntityManagerInterface $entityManager, ): Response
    {

        // Caught connect user
        $user = $this->getUser();

        // Create a new form
        $profilForm = $this->createForm(EditProfilFormType::class, $user);

        // Filling form with POST data
        $profilForm->handleRequest($request);

        if($profilForm->isSubmitted() && $profilForm->isValid()){

            $user = $profilForm->getData();

            $entityManager->persist($user);
            $entityManager->flush();

            // Success message
            $this->addFlash("success", "Profil modifié avec succès !");

            // Redirection to profil page
            return $this->redirectToRoute("main_profils");

        }

        return $this->render('main/profils_parametre.html.twig', [
            'profilForm' => $profilForm->createView()
        ]);

    }

    /**
     * Controller to edit password parameter
     */
    #[Route('/mon-profil/options/modifier-mot-de-passe/', name: 'profils_parameter_edit_password')]
    #[IsGranted("ROLE_USER")]
    public function profilEditPassword(UserPasswordHasherInterface $hasher, ManagerRegistry $doctrine, Request $request): Response
    {


        $connectedUser = $this->getUser();

        $editPasswordForm = $this->createForm(EditPasswordTypeFormType::class, $connectedUser);

        $editPasswordForm->handleRequest($request);

        if($editPasswordForm->isSubmitted() && $editPasswordForm->isValid()){

            $newPassword = $editPasswordForm->get('plainPassword')->getData();

            dump($newPassword);

            $hashOfNewPassword = $hasher->hashPassword($connectedUser, $newPassword);

            $connectedUser->setPassword($hashOfNewPassword);

            try {
                $em = $doctrine->getManager();
                $em->flush();

                $this->addFlash('success', 'Mot de passe modifié avec succès !');
            } catch (\Exception $exception){


                $this->addFlash('error', "Désolé, un problème est survenue !");

            }

           return $this->redirectToRoute("main_profils_parameter");

        }

        return $this->render('main/profils_parametre_edit_password.html.twig', [
            "editPasswordForm" => $editPasswordForm->createView(),
        ]);

    }

    /**
     * Controller to edit profil picture
     */
    #[Route('/mon-profil/options/modifier-photo-de-profil/', name: 'profils_parameter_edit_picture')]
    #[IsGranted("ROLE_USER")]
    public function profilEditPicture(Request $request, ManagerRegistry $doctrine, EntityManagerInterface $entityManager): Response
    {


        $connectedUser = $this->getUser();

        $editPictureForm = $this->createForm(EditProfilPhotoTypeFormType::class);

        $editPictureForm->handleRequest($request);

        if($editPictureForm->isSubmitted() && $editPictureForm->isValid()){

            $newPicture = $editPictureForm->get("pictureProfil")->getData();

            if($connectedUser->getPictureProfil() != null &&
            file_exists($this->getParameter('app.user.photo.directory') . $this->getUser()->getPictureProfil())
            ){
                unlink( $this->getParameter('app.user.photo.directory') . $connectedUser->getPictureProfil() );
            }

            do{
                $newFileName = md5(time() . rand() .  uniqid() ) . '.' . $newPicture->guessExtension();
            }while(file_exists($this->getParameter('app.user.photo.directory') . $newFileName));

            $connectedUser->setPictureProfil($newFileName);

            try{
                $em = $doctrine->getManager();
                $em->flush();

                $newPicture->move(
                    $this->getParameter('app.user.photo.directory'),
                    $newFileName
                );

                $this->addFlash('success',"Photo modifiée avec succès !");
            }catch (\Exception $exception){
                $this->addFlash('success',"Désolé, un problème est survenue !");
            }

        }

        return $this->render('main/profils_parametre_edit_picture.html.twig', [
            "editPictureForm"=>$editPictureForm->createView()
        ]);

    }



    /**
     * Controller to profil personal recipe
     */
    #[Route('/mon-profil/mes-recettes/', name: 'profils_personal_recipe')]
    #[IsGranted("ROLE_USER")]
    public function profilsPersonalRecipe(ManagerRegistry $doctrine, Request $request, PaginatorInterface $paginator): Response
    {

        $requestedPage = $request->query->getInt('page', 1);

        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $em = $doctrine->getManager();

        $query = $em->createQuery('SELECT a FROM App\Entity\Recipe a ORDER BY a.publicationDate DESC');

        $recipes = $paginator->paginate(
            $query,
            $requestedPage,
            9,
        );

        return $this->render('main/profils_personal_recipe.html.twig', [
            "recipes" => $recipes,
        ]);
    }


    /**
     * Controller to profil favorite recipe
     */
    #[Route('/mon-profil/mes-recettes-favorites/', name: 'profils_favorite_recipe')]
    #[IsGranted("ROLE_USER")]
    public function profilsFavoriteRecipe(): Response
    {
        return $this->render('main/profils_favorite_recipe.html.twig');
    }


    /**
     * Controller to delete profil
     */
    #[Route('/mon-profil/supprimer-mon-compte/', name: 'profils_delete')]
    #[IsGranted("ROLE_USER")]
    public function profilsDelete(Request $request, UserRepository $userRepository, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();

        if($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token')) ||
            $this->isCsrfTokenValid('delete'.$user->getGoogleId(), $request->request->get('_token'))
        ){

            $this->container->get('security.csrf.token_storage')->setToken(null);
            $userRepository->remove($user->getId(), true);

        }

        $session = new Session();
        $session->invalidate();

        try{
            $em = $doctrine->getManager();

            $em->getRepository(User::class)->findOneById($user->getId());

            $em->remove($user);

            $em->flush();

            $this->addFlash('success', "Votre compte à été supprimé avec succès !");

        }catch (\Exception $exception){

            $this->addFlash('error', "Désolé, un problème est survenue !");

        }

        return $this->redirectToRoute('main_home');
    }



    /*
     * contrôleur de la page catégories
     */
    #[Route('/categories/', name: 'categories')]
    public function categories(): Response
    {

        return $this->render('main/categories.html.twig');
    }



    /**
     * contrôleur de la page contact
     */
    #[Route('/contact/', name: 'contact')]
    public function contact(): Response
    {

        return $this->render('main/contact.html.twig');
    }



    /**
     * contrôleur de la page credit images
     */
    #[Route('/credit-images/', name: 'credit-images')]
    public function credit(): Response
    {

        return $this->render('main/credit-images.html.twig');
    }


}
