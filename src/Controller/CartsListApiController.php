<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\CartRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/carts', name: 'app_api_v1_carts_list', methods: ['GET'])]
class CartsListApiController extends AbstractController
{
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
     * Handles the HTTP GET request to fetch all cart entities.
     *
     * This method is invoked when the route '/api/v1/carts' is accessed with a GET request.
     * It retrieves all cart entities from the repository and returns them in a JSON response.
     * In case of an error, it logs the error and returns an internal server error response.
     *
     * @param Request $request the HTTP request object containing request information
     *
     * @return JsonResponse a JSON response containing the list of carts or an error message
     */
    #[Cache(maxage: 0, public: false, mustRevalidate: true)]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $carts = $this->repository->findAll();
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
                    'items' => $carts,
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            $this->logger->error('Error fetching carts: ' . $e->getMessage());

            return $this->json(['error' => 'Failed to get carts'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
