<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\FormError;
use App\Recaptcha\RecaptchaValidator;
use App\Form\RegistrationFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{


    /**
     * Contrôleur de la page d'inscription
    */
    #[Route('/creer-un-compte/', name: 'app_register')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             EntityManagerInterface $entityManager,
                             RecaptchaValidator $recaptcha): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if($this->getUser()){
            //TODO: revoir la route de redirection quand le main_home sera crée
            return $this->redirectToRoute('main_home');
        }

        // Création d'un nouvel utilisateur
        $user = new User();

        // Création d'un nouveau formulaire d'inscription
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Remplissage du formulaire avec des données POST
        $form->handleRequest($request);

        // Si le formulaire est envoyé
        if($form->isSubmitted()){

            // Récupération de la valeur du captcha
            $captchaResponse = $request->request->get('g-recaptcha-response', null);

            // Récupération de l'adresse ip
            $ip = $request->server->get('REMOTE_ADDR');

            // Si le captcha est null ou si il est invalide, affichage message d'erreur qui ne valide pas le formulaire
            if($captchaResponse == null || !$recaptcha->verify($captchaResponse, $ip)){
                // Ajout du message d'erreur
                $form->addError( new FormError('Merci de remplir le captcha de sécurité'));
            }

            // Si aucune erreur dans le formulaire
            if($form->isValid()){
                /** @var User $user */
                $user = $form->getData();
                // Hydratation des données non hydratée par le formulaire
                $user
                    //Hash du mot de passe
                    ->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('plainPassword')->getData()
                        )
                    )
                    //Hydratation date d'inscription
//                    ->setRegistrationDate(new \DateTime());
                ;
                // Image de profil
                /** @var UploadedFile $photo */
                $photoFile = $form->get('pictureProfil')->getData();

                if($photoFile){
                    $newPhotoFilename = uniqid().'.'.$photoFile->guessExtension();

                    // Déplacez le fichier dans le répertoire où la photo est stockée
                    try {
                        $photoFile->move(
                            $this->getParameter('app.user.photo.directory'),
                            $newPhotoFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', "Désolé, un problème est survenue !");
                    }

                    $user->setPictureProfil($newPhotoFilename);
                }


                try {
                    //Envoie à la BDD
                    $entityManager->persist($user);
                    $entityManager->flush();

                    //Message flash de réussite
                    //TODO: a vérifier quand on aura fait les message flash
                    $this->addFlash('success', 'Votre compte a été créé avec succès !');
                }catch (\Exception $exception){
                    $this->addFlash('error', 'Désolé, une erreur est survenue !');
                }

                // Ceci est la methode apprise en cours pour excétuer la meme ligne de code au dessus
                //if($user->getPictureProfil() !== null && file_exists($this->getParameter(('app.user.photo.directory') . $user->getPictureProfil())){
//                    $filename = $this->getParameter('app.user.photo.directory') . $user->getPictureProfil();
//                unlink($filename);
//            }

//                do{
//                    $newFileName = md5( random_bytes(100)). '.' . $photo->guessExtension();
//                }while( file_exists( $this->getParameter('app.user.photo.directory') . $newFileName ));

//                $this->getUser()->setPictureProfil($newFileName);

//                $em = $doctrine->getManager();
//                $em->flush();

//                $photo->move(
//                    $this->getParameter('app.user.photo.directory'),
//                    $newFileName
//                );

                // Redirection de l'utilisateur sur la page de connexion
               return $this->redirectToRoute('app_login');
            }
        }


        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * contrôleur de la page Conditions Générales d'Utilisation
     */
    #[Route('/cgu/', name: 'cgu')]
    public function cgu(): Response
    {
        return $this->render('registration/cgu.html.twig');
    }
}
