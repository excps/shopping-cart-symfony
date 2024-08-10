<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ControllerTrait
{
    /**
     * Sends a JSON response indicating that the requested resource was not found.
     *
     * @param Request $request the HTTP request object
     *
     * @return JsonResponse a JSON response with HTTP status 404 (Not Found) and links to related resources
     */
    private function sendNotFoundResponse(Request $request, string $message = ''): JsonResponse
    {
        $response = [
            '_type' => null,
            '_links' => $this->getBaseLinks($request),
        ];
        if ('' !== $message) {
            $response['error'] = $message;
        }

        return $this->json($response, Response::HTTP_NOT_FOUND);
    }

    /**
     * Sends a JSON response indicating that the requested resource was not found.
     *
     * @param Request $request the HTTP request object
     *
     * @return JsonResponse a JSON response with HTTP status 404 (Not Found) and links to related resources
     */
    private function sendCartBadRequestResponse(Request $request, string $message = ''): JsonResponse
    {
        $response = [
            '_type' => null,
            '_links' => $this->getBaseLinks($request),
        ];
        if ('' !== $message) {
            $response['error'] = $message;
        }

        return $this->json($response, Response::HTTP_BAD_REQUEST);
    }

    /**
     * Generates an array of base links for the API response.
     *
     * This function constructs an array of links that provide information about
     * related resources in the API, such as the list of all carts and the ability
     * to add a new cart.
     *
     * @param Request $request the HTTP request object, used to generate the base URL
     *
     * @return array<int, array<string, string>> an array of links, each containing the href, method, rel, and title
     */
    private function getBaseLinks(Request $request): array
    {
        return [
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
        ];
    }
}
