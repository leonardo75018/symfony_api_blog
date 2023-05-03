<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;



class UserController extends AbstractController
{
    #[Route('/login', name: 'login', methods: ["POST"])]
    public function index(Request $req, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $em->getRepository(User::class)->findOneBy(["email" => $req->get("email")]);

        if ($user == null) {
            return new JsonResponse("User not found", 400);
        }

        if ($req->get("pwd") == null || !$userPasswordHasher->isPasswordValid($user, $req->get("pwd"))) {
            return new JsonResponse("Invalide credentitiels", 400);
        }


        $key = $this->getParameter("jwt_secret");
        $payload = [
            'iat' => time(),   // Created_at 
            "exp" => time() + 3600,  // Expiry_at,
            "roles" => $user->getRoles() // optionel information 
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        return new JsonResponse($jwt, 200);
    }
}
