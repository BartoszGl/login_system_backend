<?php

namespace App\EventListener;

use App\Entity\AuthenticationLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationFailureListener
{
    public RequestStack $requestStack;

    public EntityManagerInterface $em;



    public function __construct(RequestStack $request, EntityManagerInterface $em)
    {
        $this->requestStack = $request;
        $this->em = $em;
    }
    /**
     * Reakcja na niepomyślne zalogowanie użytkownika do systemu 
     * @param AuthenticationFailureEvent $event
     * @return void
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {

        // Wywołuję funkcję zapisującą niepomyślne logowanie do systemu
        $this->loggingAuthenticationFailure($event);

        $response = new JWTAuthenticationFailureResponse("Bad credentials, please verify that your email/password are correctly set");

        $event->setResponse($response);
    }


    /**
     * Loguję w tym miejscu zakończone porażką logowania użytkowników do systemu. Dane mogą być wykorzystane do późniejszej analizy 
     * @param AuthenticationFailureEvent $event
     * @return void
     */
    private function loggingAuthenticationFailure(AuthenticationFailureEvent $event): void
    {
        // Aktualny request, potrzebny jest aby otrzymać informacje o ip klienta
        $request = $this->requestStack->getCurrentRequest();

        // Tworzę message, który zostanie następnie zapisany w bazie danych
        $errorMessage = $event->getException()->getMessage();
        if (!empty($event->getException()->getPrevious())) {
            $errorMessage = $event->getException()->getPrevious()->getMessage() . ' ' . $event->getException()->getMessage();
        }

        // Zapisuję log w bazie danych
        $authenticationLog = new AuthenticationLog();
        $authenticationLog->setMessage($errorMessage);
        $authenticationLog->setIpAddress($request->getClientIp());
        $authenticationLog->setDate(new DateTime());
        $authenticationLog->setIsSuccess(0);
        $this->em->persist($authenticationLog);
        $this->em->flush();
    }
}
