<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LearnMoreController extends AbstractController
{
    #[Route('/learn-more', name: 'app__learn_more')]
    public function index(): Response
    {
        return $this->render('learn_more/index.html.twig', [
            'controller_name' => '$LearnMoreController',
        ]);
    }
}
