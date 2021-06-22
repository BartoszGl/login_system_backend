<?php

namespace App\EventListener;

use App\Entity\AuthenticationLog;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\HttpFoundation\RequestStack;
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
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $data['userData'] = array(
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        );
        $this->loggingAuthenticationSuccess();
        $event->setData($data);
    }

    private function loggingAuthenticationSuccess()
    {
        $request = $this->requestStack->getCurrentRequest();
        $authenticationLog = new AuthenticationLog();
        $authenticationLog->setMessage('Success');
        $authenticationLog->setIpAddress($request->getClientIp());
        $authenticationLog->setIsSuccess(1);
        $authenticationLog->setDate(new DateTime());
        $this->em->persist($authenticationLog);
        $this->em->flush();
    }
}
