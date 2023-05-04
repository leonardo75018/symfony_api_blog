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

    #[Route('/category', name: 'app_categoryadd', methods: ["POST"])]
    public function add(EntityManagerInterface $em, Request $request, ValidatorInterface $v, LoggerInterface $l): Response
    {

        //Verify if user have authorization 
        $headers = $request->headers->all();

        if (isset($headers["token"]) && !empty($headers["token"])) {
            $jwt = current($headers["token"]); //Récupére la cellule 0 avec current()
            $key = $this->getParameter("jwt_secret");

            try {
                $decoded = JWT::decode($jwt, new Key($key, "HS256"));
                $user =  $decoded->user;
                $l->error(json_encode($user));
            } catch (\Exception $e) {
                return new JsonResponse($e->getMessage(), 403);
            }

            //If user have admin role -> create category else return 
            if ($decoded->roles != null &&  in_array("ROLE_USER", $decoded->roles)) {

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


        return new JsonResponse("Access denied", 403);
    }


    #[Route('/category/{id}', name: 'app_categoryupdate', methods: ["PATCH"])]
    public function update(Category $category = null, EntityManagerInterface $em, Request $request, ValidatorInterface $v): Response
    {
        if ($category === null) {
            return new JsonResponse("Catégorie introuvale", 404);
        }


        $params = 0;

        //On garde si le name reçu n'est pas definit
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

            //Pesist if !novalide 
            $em->persist($category);
            $em->flush();
        } else {
            return new JsonResponse("Empty", 200);
        }

        return new JsonResponse("Success", 200);
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
    public function delete(Category $category = null, EntityManagerInterface $em,): Response
    {
        if ($category == null) {
            return new JsonResponse("Category not found", 404);
        }
        $em->remove($category);
        $em->flush();

        return new JsonResponse("Category deleted", 201);
    }

    #[Route('/file', name: 'upload_file', methods: ["POST"])]
    public function fichier(Request $r): Response
    {
        $image = $r->files->get("image");

        if ($image) {

            // this is needed to safely include the file name as part of the URL
            $newFilename =  uniqid() . '.' . $image->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $image->move(
                    $this->getParameter('upload_directory'),
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                return new JsonResponse($e->getMessage(), 400);
            }

            // updates the 'brochureFilename' property to store the PDF file name
            // instead of its content
            return new JsonResponse("Fichier uploadé", 200);
        }
        return new JsonResponse("Aucun fichier reçu", 400);
    }
}
