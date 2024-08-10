<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts/{id<\d+>}', name: 'app_api_v1_carts_delete', methods: ['DELETE'])]
class CartsDeleteApiController extends AbstractController
{
    use ControllerTrait;

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
     * Handles the deletion of a cart entity.
     *
     * This method is invoked when a DELETE request is made to the specified route.
     * It attempts to find and delete the cart entity with the given ID.
     * If the cart is not found, a not found response is sent.
     * If an error occurs during the process, an internal server error response is returned.
     *
     * @param Request $request the HTTP request object containing the cart ID
     *
     * @return JsonResponse a JSON response indicating the result of the delete operation
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $id = (int) $request->get('id');
            $cart = $this->repository->findById($id);
            if (!$cart instanceof Cart) {
                $this->sendNotFoundResponse($request);
            } else {
                $this->repository->removeCart($cart);
            }

            return $this->json(
                [
                    '_links' => [
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
                                $this->generateUrl('app_api_v1_carts_create'),
                            'method' => 'Post',
                            'rel' => 'Cart',
                            'title' => 'Add Cart',
                        ],
                    ],
                ],
                Response::HTTP_NO_CONTENT
            );
        } catch (\Exception $e) {
            $this->logger->error('Error creating cart: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to delete cart.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
