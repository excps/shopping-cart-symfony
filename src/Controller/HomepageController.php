<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * This function is responsible for rendering the homepage of the application.
 *
 * @return Response the rendered homepage as a Symfony Response object
 */
class HomepageController extends AbstractController
{
    /**
     * This function is responsible for rendering the homepage of the application.
     *
     * @return Response the rendered homepage as a Symfony Response object
     */
    #[Route('/', name: 'app_homepage', methods: ['GET'])]
    public function index(): Response
    {
        return new Response('Hello, Cart!');
    }
}
