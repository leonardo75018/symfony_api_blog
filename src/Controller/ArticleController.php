<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ArticleController extends AbstractController
{

    #[Route('/article', name: 'fin_articles', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findall();
        return new JsonResponse($articles);
    }

    #[Route('/article', name: 'add_article', methods: ["POST"])]
    public function createArticle(EntityManagerInterface $em, Request $request): Response
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

                $category = $em->getRepository(Category::class)->find($request->get("category"));
                if ($category === null) {
                    return new JsonResponse("Cette categorie n'est pas valide", 404);
                }

                $article = new Article();
                $article->setTitle($request->get("title"))
                    ->setContent($request->get("content"))
                    ->setCategory($category)
                    ->setAuthor($user);

                $em->persist($article);
                $em->flush();

                return new JsonResponse("success", 200);
            }
        }


        return new JsonResponse("Access denied", 403);
    }
}
