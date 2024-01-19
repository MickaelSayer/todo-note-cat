<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReactController extends AbstractController
{
    #[Route('/{ReactRouter}', name: 'app_react_router', requirements: ['ReactRouter' => '^(?!api\/).*$'])]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }
}
