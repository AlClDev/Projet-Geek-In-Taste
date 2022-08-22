<?php
# src/Security/GoogleAuthenticator.php
namespace App\Security;

use App\Entity\User;
use League\OAuth2\Client\Provider\GoogleUser;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    private ClientRegistry $clientRegistry;
    private EntityManagerInterface $entityManager;
    private RouterInterface $router;

    public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $entityManager, RouterInterface $router)
    {
        // Compte google
        $this->clientRegistry = $clientRegistry;
        // Envoie vers la BDD
        $this->entityManager = $entityManager;
        // redirection
        $this->router = $router;
    }

    public function supports(Request $request): ?bool
    {
        // Vérification: si on est sur la bonne route, lancement du GoogleAuthentificator
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    // Méthode permettant l'authentification via un compte Google et la récupération des données de l'utilisateur Google
    public function authenticate(Request $request): Passport
    {
        // Données renvoyées par google
        $client = $this->clientRegistry->getClient('google');

        // Récupération du token de Google
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            // initialisation du nouveau badge utilisateur
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */

                // Récupération des informations client dans le token de google
                $googleUser = $client->fetchUserFromToken($accessToken);
                $email = $googleUser->getEmail();

                // have they logged in with Google before? Easy!
                $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['googleId' => $googleUser->getId()]);

                //Vérification de l'existence du l'utilisateur sinon création de l'utilisateur
                if (!$existingUser) {
                    $existingUser = new User();
                    $existingUser
                    // Données que l'on souhaite récupérer
                        ->setEmail($email)
                        ->setPseudonym($googleUser->getName())
                        ->setRegistrationDate( new \DateTime() )
                        ->setGoogleId($googleUser->getId())
                    ;
                    $this->entityManager->persist($existingUser);
                }

                $this->entityManager->flush();

                return $existingUser;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Route de Redirection si l'authentification est bonne
        return new RedirectResponse(
            $this->router->generate('main_home')
        );

        // or, on success, let the request continue to be handled by the controller
        //return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        // Si l'authentification n'est pas bonne
        return new Response($message, Response::HTTP_FORBIDDEN);
    }

//    public function start(Request $request, AuthenticationException $authException = null): Response
//    {
//        /*
//         * If you would like this class to control what happens when an anonymous user accesses a
//         * protected page (e.g. redirect to /login), uncomment this method and make this class
//         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
//         *
//         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
//         */
//    }
}