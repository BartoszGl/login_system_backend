<?php

namespace App\Security;

date_default_timezone_set("Europe/Berlin");

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class EmailVerifier
{
    private $verifyEmailHelper;
    private $mailer;
    private $entityManager;
    private $appUrl;
    private $kernel;

    public function __construct(
        VerifyEmailHelperInterface $helper,
        MailerInterface $mailer,
        EntityManagerInterface $manager,
        array $appUrl,
        KernelInterface $kernel
    ) {
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
        $this->entityManager = $manager;
        $this->appUrl = $appUrl;
        $this->kernel = $kernel;
    }

    /**
     * Wysyłanie emaila do użytkownika
     * @param string $verifyEmailRouteName
     * @param User $user
     * @param TemplatedEmail $email
     * 
     * @return void
     */
    public function sendEmailConfirmation(string $verifyEmailRouteName, User $user, TemplatedEmail $email): void
    {
        //Stworzenie sygnatury wysyłanego maila na podstawie: linku do strony aktywującej, id użytkownika i jego maila
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            $user->getId(),
            $user->getEmail()
        );

        $context = $email->getContext();
        $context['signedUrl'] = $this->modifyHost($signatureComponents->getSignedUrl());
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();

        $email->context($context);

        $this->mailer->send($email);
    }

    /**
     * Walidacja maila użytkowinka
     * @param Request $request
     * @param User $user
     * 
     * @return void
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(Request $request, User $user): void
    {

        $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());

        $user->setIsVerified(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Ze względu na to, że korzystam z zewnętrznej biblioteki do tworzenia "signed mail url", konieczna jest modyfikacja hosta w linku
     * w emailu weryfikacyjnym, informacje o poszczególnych route na front/backend znajdują się w parametrach w service
     * 
     * @param string $url
     * 
     * @return string
     */
    public function modifyHost(string $url): string
    {
        $env = $this->kernel->getEnvironment();
        return str_replace($this->appUrl["backend_" . $env] . '/api', $this->appUrl["front_" . $env], $url);
    }
}
