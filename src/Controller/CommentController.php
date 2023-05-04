<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Coment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(EntityManagerInterface $em): Response
    {
        $comments = $em->getRepository(Coment::class)->findall();
        return new JsonResponse($comments);
    }

    #[Route('/comment/{article_id}', name: 'create_comment', methods: ["POST"])]
    public function createComent(Article $article = null, EntityManagerInterface $em, Request $request): Response
    {

        //Verify if user have authorization 
        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]); //Récupére la cellule 0 avec current()
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }

            //If user have admin role -> create category else return 
            if ($decoded->roles != null &&  in_array("ROLE_USER", $decoded->roles)) {

                $user = $em->getRepository(User::class)->findOneBy(["id" => $decoded->user]);

                if ($user == null) {
                    return new JsonResponse("User not found", 400);
                }

                // $article = $em->getRepository(Article::class)->find($request->get("article"));
                if ($article === null) {
                    return new JsonResponse("Cette article n'existe pas", 404);
                }

                $coment = new Coment();
                $coment->setContent($request->get("coment"))
                    ->setArticle($article)
                    ->setAuthor($user);

                $em->persist($coment);
                $em->flush();

                return new JsonResponse("success", 200);
            }
        }


        return new JsonResponse("Access denied", 403);
    }



    #[Route('/comment/{id}', name: 'update_comment_state', methods: ["GET"])]
    public function updateComentState(Coment $comment = null, EntityManagerInterface $em, Request $request): Response
    {

        //Verify if user have authorization 
        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]); //Récupére la cellule 0 avec current()
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }

            //If user have admin role -> create category else return 
            if ($decoded->roles != null &&  in_array("ROLE_USER", $decoded->roles)) {

                $user = $em->getRepository(User::class)->findOneBy(["id" => $decoded->user]);

                if ($user == null) {
                    return new JsonResponse("User not found", 400);
                }

                if ($comment === null) {
                    return new JsonResponse("Comment not found", 404);
                }

                $comment->setState(false);
                $em->persist($comment);
                $em->flush();

                return new JsonResponse("success", 200);
            }
        }


        return new JsonResponse("Access denied", 403);
    }
}
