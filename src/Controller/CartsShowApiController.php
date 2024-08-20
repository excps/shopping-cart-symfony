<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts/{id<\d+>}', name: 'app_api_v1_carts_show', methods: ['GET'])]
class CartsShowApiController extends AbstractController
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
        private readonly CartRepository $repository,
    ) {
    }

    /**
     * Handles the request to fetch a cart by its ID.
     *
     * This method is invoked when a GET request is made to the route defined for fetching a cart.
     * It retrieves the cart from the repository and returns it in a JSON response.
     * If the cart is not found, it returns a 404 Not Found response.
     * In case of an error, it logs the error and returns a 500 Internal Server Error response.
     *
     * @param Request $request the HTTP request object containing the cart ID
     *
     * @return JsonResponse a JSON response containing the cart data or an error message
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $id = (int) $request->get('id');
            $cart = $this->repository->findById($id);
            if (!$cart instanceof Cart) {
                return $this->sendNotFoundResponse($request);
            }

            return $this->json(
                [
                    '_type' => 'Cart',
                    '_links' => [
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_show', ['id' => $cart->getId()]),
                            'method' => 'GET',
                            'rel' => 'self',
                            'title' => 'Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_delete', ['id' => $cart->getId()]),
                            'method' => 'DELETE',
                            'rel' => 'delete',
                            'title' => 'Delete Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_list'),
                            'method' => 'GET',
                            'rel' => 'carts',
                            'title' => 'All Carts',
                        ],
                    ],
                    'cart' => $cart,
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching cart: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to get cart.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
