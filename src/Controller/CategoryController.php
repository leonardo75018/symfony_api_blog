<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;



class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {

        $category = $em->getRepository(Category::class)->findRecentCategories();
        return new JsonResponse($category);
    }

    #[Route('/category', name: 'add_category', methods: ["POST"])]
    public function add(EntityManagerInterface $em, Request $request, ValidatorInterface $v): Response
    {
        //Verify if user have authorization 
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

            //If user have admin role -> create category else return 
            if ($decoded->roles != null &&  in_array("ROLE_ADMIN", $decoded->roles)) {

                $category = new Category();
                $category->setName($request->get("name"));

                $errors = $v->validate($category);
                $e_list = [];


                if (count($errors) > 0) {
                    foreach ($errors as $e) {
                        $e_list[] = $e->getMessage();
                    }
                    return new JsonResponse($e_list, 400);
                }

                $em->persist($category);
                $em->flush();

                return new JsonResponse("success", 200);
            }
        }
        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }


    #[Route('/category/{id}', name: 'app_categoryupdate', methods: ["PATCH"])]
    public function update(Category $category = null, EntityManagerInterface $em, Request $request, ValidatorInterface $v): Response
    {
        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]);
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
                $user =  $decoded->user;
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }

            if ($decoded->roles != null &&  in_array("ROLE_ADMIN", $decoded->roles)) {
                if ($category === null) {
                    return new JsonResponse("Catégorie introuvale", 404);
                }

                $params = 0;

                if ($request->get("name") != null) {
                    $params++;
                    $category->setName($request->get("name"));
                }

                if ($params > 0) {
                    //Validate data
                    $errors = $v->validate($category);
                    $e_list = [];

                    if (count($errors) > 0) {
                        foreach ($errors as $e) {
                            $e_list[] = $e->getMessage();
                        }
                        return new JsonResponse($e_list, 400);
                    }

                    $em->persist($category);
                    $em->flush();
                } else {
                    return new JsonResponse("Empty", 200);
                }
                return new JsonResponse("Success", 200);
            }
        }
        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }


    #[Route('/category/{id}', name: 'one_category', methods: ["POST"])]
    public function getCategoryById($id, EntityManagerInterface $em)
    {
        $category = $em->getRepository(Category::class)->findOneById($id);

        if ($category == null) {
            return new JsonResponse("Category not found", 400);
        }
        return new JsonResponse($category, 200);
    }

    #[Route('/category/{id}', name: 'category_delete', methods: ["DELETE"])]
    public function delete(Category $category = null, EntityManagerInterface $em, Request $request,): Response
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
                if ($category == null) {
                    return new JsonResponse("Category not found", 404);
                }
                $em->remove($category);
                $em->flush();

                return new JsonResponse("Category deleted", 201);
            }
        }

        return new JsonResponse("Access denied. This endpoint is only accessible to users with admin privileges.", 403);
    }
}
