<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartItemRepository;
use App\Repository\CartRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts/{id<\d+>}/items/{item_id<\d+>}', name: 'app_api_v1_carts_update_item', methods: ['PUT'])]
class CartItemUpdateController extends AbstractController
{
    use ControllerTrait;

    /**
     * Constructor for the CartsApiController.
     *
     * Initializes the LoggerInterface, CartRepository, and CartItemRepository dependencies.
     *
     * @param LoggerInterface    $logger          the logger instance for logging errors and information
     * @param CartRepository     $repository      the repository instance for managing cart entities
     * @param CartItemRepository $item_repository the repository instance for managing cart item entities
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CartRepository $repository,
        private readonly CartItemRepository $item_repository,
    ) {
    }

    /**
     * Handles the update of a cart item.
     *
     * This method is invoked when a PUT request is made to update a specific item in a cart.
     * It retrieves the cart and the item, updates the item's properties based on the request data,
     * and saves the changes to the repository.
     *
     * @param Request $request the HTTP request object containing the cart ID, item ID, and update data
     *
     * @return JsonResponse a JSON response containing the updated cart information or an error message
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $cart_id = (int) $request->get('id');
            $cart = $this->repository->findById($cart_id);
            if (!$cart instanceof Cart) {
                return $this->sendNotFoundResponse($request, 'Cart not found.');
            }

            $requestData = json_decode($request->getContent(), true);
            // todo add validation here

            $item_id = (int) $request->get('item_id');
            $cart_item = $this->item_repository->findCartItem($item_id, $cart_id);

            if (!$cart_item instanceof CartItem) {
                return $this->sendNotFoundResponse($request, 'Cart item not found.');
            }

            if (isset($requestData['code'])) {
                $cart_item->setCode((string) $requestData['code']);
            }
            if (isset($requestData['name'])) {
                $cart_item->setName((string) $requestData['name']);
            }
            if (isset($requestData['price'])) {
                $cart_item->setPrice((int) $requestData['price']);
            }
            if (isset($requestData['quantity'])) {
                $cart_item->setQuantity((int) $requestData['quantity']);
            }
            $this->item_repository->save($cart_item);
            $this->repository->save($cart);

            return $this->json(
                [
                    '_type' => 'Cart',
                    '_links' => [
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_update_item',
                                    ['id' => $cart_id, 'item_id' => $item_id]
                                ),
                            'method' => 'PUT',
                            'rel' => 'self',
                            'title' => 'Update Item',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_delete_item',
                                    ['id' => $cart_id, 'item_id' => $item_id]
                                ),
                            'method' => 'DELETE',
                            'rel' => 'item_delete',
                            'title' => 'Delete Item',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_add_item',
                                    ['id' => $cart_id]
                                ),
                            'method' => 'POST',
                            'rel' => 'item_add',
                            'title' => 'Add Item',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_show',
                                    ['id' => $cart_id]
                                ),
                            'method' => 'GET',
                            'rel' => 'cart',
                            'title' => 'Show Cart',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_delete',
                                    ['id' => $cart_id]
                                ),
                            'method' => 'DELETE',
                            'rel' => 'cart_delete',
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
            $this->logger->error('Error updating cart item: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to update cart item.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
