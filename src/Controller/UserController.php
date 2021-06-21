<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/api/current-user", name="current_user",  methods={"GET"})
     */
    public function currentUser()
    {
        $user = $this->getUser();
        $userData = [
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ];
        return $this->json($userData, 200);
    }

    /**
     * @Route("/api/users-list", name="users_list",  methods={"GET"})
     */
    public function getUsersList()
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var UserRepository $users
         */
        $users = $em->getRepository(User::class);
        return $this->json($users->getAllUsers(), 200);
    }
}
