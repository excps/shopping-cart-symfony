<?php

namespace App\Controller;

use App\Repository\CartRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts', name: 'app_api_v1_carts_create', methods: ['POST'])]
class CartsAddApiController extends AbstractController
{
    /**
     * Constructor for the CartsApiController.
     *
     * Initializes the LoggerInterface, CartRepository, and ManagerRegistry dependencies.
     *
     * @param LoggerInterface $logger     the logger instance for logging errors and information
     * @param CartRepository  $repository the repository instance for managing cart entities
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CartRepository $repository
    ) {
    }

    /**
     * Create a new cart.
     *
     * This method creates a new cart entity and persists it to the database.
     *
     * @return JsonResponse a JSON response containing the newly created cart and its links
     *
     * @throws \Exception if an error occurs while creating the cart
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $cart = $this->repository->createCart();
            $cart_id = (int) $cart->getId();

            return $this->json(
                [
                    '_type' => 'Cart',
                    '_links' => [
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_create'),
                            'method' => 'POST',
                            'rel' => 'self',
                            'title' => 'New Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_show', ['id' => $cart_id]),
                            'method' => 'GET',
                            'rel' => 'cart',
                            'title' => 'Show Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_list'),
                            'method' => 'GET',
                            'rel' => 'carts',
                            'title' => 'All Carts',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_delete', ['id' => $cart_id]),
                            'method' => 'DELETE',
                            'rel' => 'delete',
                            'title' => 'Delete Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_add_item', ['id' => $cart_id]),
                            'method' => 'Post',
                            'rel' => 'Item',
                            'title' => 'Add Item',
                        ],
                    ],
                    'cart' => $cart,
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            $this->logger->error('Error creating cart: ' . $e->getMessage());

            return $this->json(
                ['error' => 'Failed to create cart.' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
