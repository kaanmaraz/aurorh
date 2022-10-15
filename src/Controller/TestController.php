<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/test', name: 'app_test')]
    public function index(CandidatRepository $candidatRepository): Response
    {
        $result = $candidatRepository->findIdSuivant(20); 
        var_dump($result);
        return new Response("Ok", 200);
    }
}
