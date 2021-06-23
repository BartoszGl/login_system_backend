<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * Z powodów bezpieczeństwa w local storage na froncie przechowywany jest jedynie token, 
     * klient musi zapytać o swoje dane za każdą zmianą widoku na froncie, w ten sposób
     * upewniam się, że klient jest zawsze tym za kogo się podaje i ma odpowiednie uprawnienia.
     * Podczas walidacji tokena system dowiaduje się kto jest autorem requesta, dzieje się to "wyżej" nad 
     * controllerem, w kernelu, dlatego już w tym miejscu wiem kto jest właścicielem tokena, nie potrzebuję w związku z tym
     * żadnego parametra z reqestem w poniższej.
     * 
     * @Route("/api/user/current", name="current_user",  methods={"GET"})
     * @return JsonResponse
     */
    public function currentUser(): JsonResponse
    {
        $user = $this->getUser();
        $userData = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'isVerified' => $user->isVerified()
        ];
        return $this->json($userData, 200);
    }
}
