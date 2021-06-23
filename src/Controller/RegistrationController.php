<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * Rejestracja użytkownika
     * @Route("/api/register", name="app_register",  methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * 
     * @return JsonResponse
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        //Tworzenie formularza użytkownika, składa się na niego email użytkownika i jego hasło
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->submit($data);

        //Sprawdzam czy wysłany mail i hasło są poprawne
        if ($form->isSubmitted() && $form->isValid()) {

            // Szyfrowanie hasła
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            //Tworzenie maila użytkownika
            $user->setEmail($form->get('email')->getData());
            $user->setRoles(['ROLE_USER']);

            //Zapisywanie danych w bazie
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            //Stworzenie maila z syganturą, który zostanie następnie wysłany do użytkownika
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('loginsystemtest157@gmail.com', 'Login System Test'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->json(['result' => 'We have sent a confirmation link to your mail'], 200);
        }

        //Zbieram błędy które powstały w trakcie tworzenia formularza i wysyłam response
        $errors = '';
        foreach ($form->getErrors(true) as $formError) {
            $errors .= $formError->getMessage() . "\n";
        }

        return $this->json($errors, 400);
    }

    /**
     * Weryfikacja maila użytkownika
     * @Route("/api/verify/email", name="app_verify_email",  methods={"POST"})
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function verifyUserEmail(Request $request): JsonResponse
    {
        // Walidacja maila użytkownika, jeżeli się nie powiedzie to klient dostanie wiadomość z informacją o błędzie
        // Ze względów bezpieczeństwa użytkownik musi się zalogować przed tym, jak będzie miał możliwość potwierdzenia swojego maila
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            return $this->json($exception->getReason(), 401);
        }

        return $this->json('Your email address has been verified.');
    }
}
