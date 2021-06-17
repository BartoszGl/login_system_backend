<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/api/registration", name="registration")
     */
    public function registration(Request $request): Response
    {
        dd($request);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/RegistrationController.php',
        ]);
    }
}
