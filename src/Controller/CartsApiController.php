<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts')]
class CartsApiController extends AbstractController
{
    private LoggerInterface $logger;
    private CartRepository $cartRepository;
    private ManagerRegistry $registry;

    public function __construct(LoggerInterface $logger, CartRepository $repository, ManagerRegistry $registry)
    {
        $this->logger = $logger;
        $this->cartRepository = $repository;
        $this->registry = $registry;
    }

    #[Route('/test', name: 'app_api_v1_test', methods: ['POST'])]
    public function testAction(): JsonResponse
    {
        $carts = $this->cartRepository->findAll();

//        $entityManager = $this->registry->getManager();

//        $cart = new Cart();
//        $cart->setCode('test');
//        $cart->getTotalPrice();
//        $cart->setCreatedAt(new \DateTimeImmutable());
//
//        // tell Doctrine you want to (eventually) save the Product (no queries yet)
//        $entityManager->persist($cart);
//
//        // actually executes the queries (i.e. the INSERT query)
//        $entityManager->flush();

        return $this->json(['msg' => 'test', 'data' => $carts], 200);
    }

    #[Route('', name: 'app_api_v1_addCart', methods: ['POST'])]
    public function createNewCart(): JsonResponse
    {
        try {
            $entityManager = $this->registry->getManager();
            $cart = $this->cartRepository->createCart($entityManager);
            // Persist the cart to the database
            $entityManager->persist($cart);
            // Flush the changes to the database
            $entityManager->flush();

            return $this->json($cart);
        } catch (\Exception $e) {
            $this->logger->error('Error creating cart: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to create cart: ' . $e->getMessage()], 500);
        }
    }

//
//    #[Route('', name: 'app_api_v1_carts_list', methods: ['GET'])]
//    public function showCarts(): JsonResponse
//    {
//        try {
//            $carts = $this->cartRepository->findAll();
//        } catch (\Exception $e) {
//            $this->logger->error('Error fetching carts: ' . $e->getMessage());
//
//            return $this->json(['error' => 'Failed to get carts'], 500);
//        }
//
//        return $this->json($carts, 200);
//    }
//
//    #[Route('/{cart_id<\d+>}', name: 'app_api_v1_carts_show_cart', methods: ['GET'])]
//    public function getCart(?int $cart_id = null): JsonResponse
//    {
//        // Todo implement functionality to fetch all carts
//        // return status code 200 with data
//        return $this->json([]);
//    }
//
//    #[Route('/{cart_id<\d+>}', name: 'app_api_v1_carts_update_cart', methods: ['PUT'])]
//    public function updateCart(): JsonResponse
//    {
//        // Todo implement functionality to update a cart
//        // return status code 201 and new cart object
//
//        return $this->json([]);
//    }
//
//    #[Route('/{cart_id<\d+>}', name: 'app_api_v1_carts_delete_cart', methods: ['DELETE'])]
//    public function deleteCart(?int $cart_id = null): JsonResponse
//    {
//        // Todo implement functionality to delete a cart
//        // return status code 200 with data
//        return $this->json([]);
//    }
//
//    #[Route('/{cart_id<\d+>}/items', name: 'app_api_v1_carts_add_cart_item', methods: ['POST'])]
//    public function addCartItem(?int $cart_id = null): JsonResponse
//    {
//        // Todo implement functionality to ad a cart item to a cart
//        // return status code 201 with data
//        return $this->json([]);
//    }
//
//    #[Route('/{cart_id<\d+>}/items/{item_id<\d+>}}', name: 'app_api_v1_carts_update_cart_item', methods: ['PUT'])]
//    public function updateCartItem(?int $cart_id = null): JsonResponse
//    {
//        // Todo implement functionality to update a cart item
//        // return status code 200 with data
//        return $this->json([]);
//    }
//
//    #[Route('/{cart_id<\d+>}/items/{item_id<\d+>}}', name: 'app_api_v1_carts_delete_cart_item', methods: ['DELETE'])]
//    public function deleteCartItem(int $cart_id, int $item_id): JsonResponse
//    {
//        // Todo implement functionality to delete cart item
//        // Return a 204 No Content response
//        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
//    }
}
