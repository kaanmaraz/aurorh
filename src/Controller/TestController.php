<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

class TestController extends AbstractController
{
    private $candidatWorkflow; 

    public function __construct(WorkflowInterface $candidatWorkflow)
    {
        $this->candidatWorkflow = $candidatWorkflow; 
    }
    #[Route('/test', name: 'app_test')]
    public function index(CandidatRepository $candidatRepository): Response
    {
        $result = $candidatRepository->findAll()[0]; 
        $reponse =  $this->candidatWorkflow->getMarking($result); 
        var_dump($reponse);
        return new Response("Ok", 200);
    }
}
