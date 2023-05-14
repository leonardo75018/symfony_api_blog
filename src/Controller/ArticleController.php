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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleController extends AbstractController
{

    #[Route('/article', name: 'find_articles', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        $articles = $em->getRepository(Article::class)->findall();
        return new JsonResponse($articles);
    }

    #[Route('/article/{id}', name: 'one_article', methods: ["POST"])]
    public function getCategoryById($id, EntityManagerInterface $em)
    {
        $article = $em->getRepository(Article::class)->findOneById($id);

        if ($article == null) {
            return new JsonResponse("Article not found", 400);
        }
        return new JsonResponse($article, 200);
    }

    #[Route('/article', name: 'add_article', methods: ["POST"])]
    public function createArticle(EntityManagerInterface $em, Request $request, ValidatorInterface $v): Response
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
            if ($decoded->roles != null &&  in_array("ROLE_ADMIN", $decoded->roles)) {

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

        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }

    #[Route('/article/{id}', name: 'update_article', methods: ["PATCH"])]
    public function updateArticle(Article $article, EntityManagerInterface $em, Request $request): Response
    {
        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]);
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }
            if ($decoded->roles != null &&  in_array("ROLE_ADMIN", $decoded->roles)) {

                $user = $em->getRepository(User::class)->findOneBy(["id" => $decoded->user]);
                if ($user == null) {
                    return new JsonResponse("User not found", 400);
                }

                $category = $em->getRepository(Category::class)->find($request->get("category"));
                if ($category === null) {
                    return new JsonResponse("Cette categorie n'est pas valide", 404);
                }

                $article->setTitle($request->get("title"))
                    ->setContent($request->get("content"))
                    ->setCategory($category)
                    ->setAuthor($user);

                $em->persist($article);
                $em->flush();

                return new JsonResponse("success", 200);
            }
        }


        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }

    #[Route('/article/{id}', name: 'delete_article', methods: ["DELETE"])]
    public function deleteArticle(Article $article = null, EntityManagerInterface $em, Request $request): Response
    {


        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]); //Récupére la cellule 0 avec current()
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
                $user =  $decoded->user;
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }

            if ($decoded->roles != null &&  in_array("ROLE_ADMIN", $decoded->roles)) {
                if ($article == null) {
                    return new JsonResponse("Article not found", 404);
                }
                $em->remove($article);
                $em->flush();

                return new JsonResponse("Article deleted", 201);
            }
        }

        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }
}
