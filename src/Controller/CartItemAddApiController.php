<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts/{id<\d+>}/items', name: 'app_api_v1_carts_add_item', methods: ['POST'])]
class CartItemAddApiController extends AbstractController
{
    use ControllerTrait;

    /**
     * Constructor for the CartsApiController.
     *
     * Initializes the LoggerInterface, CartRepository dependencies.
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
     * Handles the addition of a new item to a cart.
     *
     * This method processes the incoming request to add a new item to the specified cart.
     * It validates the request data, creates a new CartItem, and updates the cart with the new item.
     * If the cart is not found or the request data is invalid, it returns an appropriate error response.
     *
     * @param Request $request the HTTP request object containing the cart ID and item data
     *
     * @return JsonResponse a JSON response containing the updated cart data or an error message
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        if ('' === $request->getContent()) {
            return $this->sendCartBadRequestResponse($request, 'No request data provided.');
        }

        $id = (int) $request->get('id');
        $cart = $this->repository->findById($id);
        if (!$cart instanceof Cart) {
            return $this->sendNotFoundResponse($request);
        }

        try {
            $requestData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            $this->logger->error('Failed to decode JSON request data: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to decode JSON request data.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            if (empty($requestData)) {
                return $this->sendCartBadRequestResponse($request, 'Request data are empty');
            }
            $message = '';
            $validData = true;
            if (!isset($requestData['code']) || isset($requestData['code']) && !is_string($requestData['code'])) {
                $message .= 'Missing or invalid item code. ';
                $validData = false;
            }
            if (!isset($requestData['name']) || isset($requestData['name']) && !is_string($requestData['name'])) {
                $message .= 'Missing or invalid item name. ';
                $validData = false;
            }
            if (
                !isset($requestData['price'])
                || (isset($requestData['price']) && !is_int($requestData['price']))
                || $requestData['price'] <= 0
            ) {
                $message .= 'Missing or invalid item price. ';
                $validData = false;
            }
            if (
                !isset($requestData['quantity'])
                || (isset($requestData['quantity']) && !is_int($requestData['quantity']))
                || $requestData['quantity'] < 0
            ) {
                $message .= 'Missing or invalid item quantity. ';
                $validData = false;
            }
            if (!$validData) {
                return $this->json(['error' => 'Invalid request data: ' . $message], Response::HTTP_BAD_REQUEST);
            }

            $item = new CartItem();
            $item->setCode($requestData['code']);
            $item->setName($requestData['name']);
            $item->setPrice($requestData['price']);
            $item->setQuantity($requestData['quantity']);
            $item->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('UTC')));
            $updated_cart = $this->repository->addItem($cart, $item);

            return $this->json(
                [
                    '_type' => 'CartCollection',
                    '_links' => [
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_list'),
                            'method' => 'GET',
                            'rel' => 'self',
                            'title' => 'Carts',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_create'),
                            'method' => 'POST',
                            'rel' => 'new cart',
                            'title' => 'New Cart',
                        ],
                    ],
                    'cart' => $updated_cart,
                ],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            $this->logger->error('Error adding cart item: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to create cart item.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
