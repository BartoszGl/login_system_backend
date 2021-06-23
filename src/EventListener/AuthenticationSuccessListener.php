<?php

namespace App\EventListener;

use App\Entity\AuthenticationLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @param AuthenticationSuccessEvent $event
 */
class AuthenticationSuccessListener
{
    public RequestStack $requestStack;

    public EntityManagerInterface $em;


    public function __construct(RequestStack $request, EntityManagerInterface $em)
    {
        $this->requestStack = $request;
        $this->em = $em;
    }

    /**
     * Reakcja na pomyślne zalogowanie użytkownika do systemu 
     * @param AuthenticationSuccessEvent $event
     * @return void
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event): void
    {

        // Wywołuję funkcję zapisującą pomyślne logowanie do systemu

        // Zmieniam domyślny reponse lexik jwt bundle,
        $data = $event->getData();
        $user = $event->getUser();

        // Wywołuję funkcję zapisującą pomyślne logowanie do systemu
        $this->loggingAuthenticationSuccess($user->getUserIdentifier());

        if (!$user instanceof UserInterface) {
            return;
        }
        $data['userData'] = array(
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        );

        $event->setData($data);
    }



    /**
     * Loguję w tym miejscu zakończone sukcesem logowania użytkowników do systemu
     * @return void
     */
    private function loggingAuthenticationSuccess($user): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $authenticationLog = new AuthenticationLog();
        $authenticationLog->setMessage($user . ' successfully logged in');
        $authenticationLog->setIpAddress($request->getClientIp());
        $authenticationLog->setIsSuccess(1);
        $authenticationLog->setDate(new DateTime());
        $this->em->persist($authenticationLog);
        $this->em->flush();
    }
}
