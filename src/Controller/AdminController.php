<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    /**
     * Widok dla admina, pobieranie listy wszystkich użytkowników
     * 
     * @Route("/api/admin/users", name="users_list",  methods={"GET"})
     * @return JsonResponse
     */
    public function getUsersList(): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var UserRepository $users
         */
        $users = $em->getRepository(User::class);
        return $this->json($users->getAllUsers(), 200);
    }
}
