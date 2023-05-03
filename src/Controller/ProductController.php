<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Service\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product', methods: ["GET"])]
    public function index(EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findall();
        return new JsonResponse($products);
    }


    #[Route('/product', name: 'add_product', methods: ["POST"])]
    public function add(EntityManagerInterface $em, Request  $request, Validator $validator): Response
    {

        $category = $em->getRepository(Category::class)->find($request->get("category"));

        if ($category === null) {
            return new JsonResponse("Cette categorie n'est pas valide", 404);
        }

        $product = new Product();
        $product->setTitle($request->get("title"))
            ->setPrice($request->get("price"))
            ->setQuantity($request->get("quantity"))
            ->setCategory($category);


        $isValid = $validator->isValid($product);

        if ($isValid !== true) {
            return new JsonResponse($isValid, 400);
        }


        $em->persist($product);
        $em->flush();

        return new JsonResponse("success", 200);
    }

    #[Route('/product/{id}', name: 'update_product', methods: ["PATCH"])]
    public function update(Product $product = null, EntityManagerInterface $em, Request $request, ValidatorInterface $v, Validator $validator): Response
    {
        if ($product === null) {
            return new JsonResponse("Product not found", 404);
        }


        //Check if category property is not full in request
        if ($request->get("category") != null) {
            $category = $em->getRepository(Category::class)->find($request->get("category"));

            //Return error if category not exist
            if ($category === null) {
                return new JsonResponse("Cetegory not found", 404);
            }
        }


        $params = 0;

        //On garde si le name reçu n'est pas definit
        if ($request->get("title") != null) {
            $params++;
            $product->setTitle($request->get("title"))
                ->setPrice($request->get("price"))
                ->setQuantity($request->get("quantity"))
                ->setCategory($category);
        }


        if ($params > 0) {
            $params++;
            $errors = $v->validate($category);
            $e_list = [];

            if (count($errors) > 0) {
                foreach ($errors as $e) {
                    $e_list[] = $e->getMessage();
                }
                return new JsonResponse($e_list, 400);
            }



            //Verify data
            $isValid = $validator->isValid($product);

            if ($isValid !== true) {
                return new JsonResponse($isValid, 400);
            }


            //Pesist if !novalide 
            $em->persist($category);
            $em->flush();
        } else {
            return new JsonResponse("Empty", 200);
        }

        return new JsonResponse("Success", 200);
    }



    #[Route('/product/{id}', name: 'one_product', methods: ["POST"])]
    public function getCategoryById($id, EntityManagerInterface $em)
    {
        $product = $em->getRepository(Product::class)->findOneById($id);

        if ($product == null) {
            return new JsonResponse("product not found", 400);
        }
        return new JsonResponse($product, 200);
    }



    #[Route('/product/{id}', name: 'product_detele', methods: ["DELETE"])]
    public function delete(Product $product = null, EntityManagerInterface $em,): Response
    {
        if ($product == null) {
            return new JsonResponse("Product not found", 404);
        }
        $em->remove($product);
        $em->flush();

        return new JsonResponse("Product deleted", 201);
    }
}
