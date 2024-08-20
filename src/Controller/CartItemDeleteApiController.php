<?php
declare(strict_types=1);

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

#[Route('/api/v1/carts/{id<\d+>}/items/{item_id<\d+>}', name: 'app_api_v1_carts_delete_item', methods: ['DELETE'])]
class CartItemDeleteApiController extends AbstractController
{
    use ControllerTrait;

    /**
     * Constructor for the CartsApiController.
     *
     * Initializes the LoggerInterface, CartRepository dependencies.
     *
     * @param LoggerInterface    $logger          the logger instance for logging errors and information
     * @param CartRepository     $repository      the repository instance for managing cart entities
     * @param CartItemRepository $item_repository the repository instance for managing cart entities
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly CartRepository $repository,
        private readonly CartItemRepository $item_repository,
    ) {
    }

    /**
     * Handles the deletion of a cart item.
     *
     * This method is invoked when a DELETE request is made to the specified route.
     * It attempts to delete a cart item from the cart and returns a JSON response
     * with the updated cart information or an error message if the deletion fails.
     *
     * @param Request $request the HTTP request object containing the cart and item IDs
     *
     * @return JsonResponse a JSON response containing the updated cart information or an error message
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $cart_id = (int) $request->get('id');
            $item_id = (int) $request->get('item_id');
            $cart = $this->repository->findById($cart_id);
            if (!$cart instanceof Cart) {
                return $this->sendNotFoundResponse($request, 'Cart not found.');
            }

            $cart_item = $this->item_repository->findCartItem($item_id, $cart_id);
            if ($cart_item instanceof CartItem) {
                $cart = $this->repository->deleteItem($cart, $cart_item);
            }

            return $this->json(
                [
                    '_type' => 'Cart',
                    '_links' => [
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl(
                                    'app_api_v1_carts_delete_item',
                                    ['id' => $cart_id, 'item_id' => $item_id]
                                ),
                            'method' => 'DELETE',
                            'rel' => 'self',
                            'title' => 'Delete Item',
                        ],
                        [
                            'href' => $request->getScheme() . '://' .
                                $request->getHost() .
                                $this->generateUrl('app_api_v1_carts_add_item', ['id' => $cart_id]),
                            'method' => 'POST',
                            'rel' => 'Item',
                            'title' => 'Add Item',
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
                                $this->generateUrl('app_api_v1_carts_delete', ['id' => $cart_id]),
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
            $this->logger->error('Error deleting cart item: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to delete cart item.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
