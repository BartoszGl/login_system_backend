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
     * @param AuthenticationFailureEvent $event
     */
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event): void
    {
        $this->loggingAuthenticationFailure($event);
        $response = new JWTAuthenticationFailureResponse('Bad credentials, please verify that your username/password are correctly set');

        $event->setResponse($response);
    }

    private function loggingAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        $authenticationLog = new AuthenticationLog();
        $errorMessage = $event->getException()->getPrevious()->getMessage() . ' ' . $event->getException()->getMessage();
        $authenticationLog->setMessage($errorMessage);
        $authenticationLog->setIpAddress($request->getClientIp());
        $authenticationLog->setDate(new DateTime());
        $authenticationLog->setIsSuccess(0);
        $this->em->persist($authenticationLog);
        $this->em->flush();
    }
}
