<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;  

class AccueilController extends AbstractController
{
    private LoggerInterface $logger;

   
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    
    public function home(): Response
    {
        return $this->render('img/home.html.twig');
    }
}
