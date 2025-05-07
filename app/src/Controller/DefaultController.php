<?php

/**
 * Default controller.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class DefaultController.
 */
class DefaultController extends AbstractController
{
    /**
     * @return Response HTTP response
     */
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->redirectToRoute('bug_index');
    }
}
