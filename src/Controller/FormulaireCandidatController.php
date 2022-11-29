<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\Document;
use App\Form\FormulaireCandidatType;
use App\Repository\CandidatRepository;
use App\Service\GestionnaireFichiers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formulaire/candidat')]
class FormulaireCandidatController extends AbstractController
{
    #[Route('/', name: 'app_formulaire_candidat')]
    public function index(Request $request, CandidatRepository $candidatRepository, GestionnaireFichiers $gestionnaireFichiers): Response
    {
        $candidat = $candidatRepository->findOneBy(["email" => $this->getUser()->getEmail()]); 
        $form = $this->createForm(FormulaireCandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gestionnaireFichiers->enregistrerTout($candidat,$form->all()); 
            // $this->candidatWorkflow->apply($candidat, 'to_attente_envoi_mail_form'); 
            $candidatRepository->add($candidat, true);
            return $this->render('formulaire_candidat/finish_page.html.twig'); 
            
        }
        return $this->render('formulaire_candidat/index.html.twig', [
            'candidat' => $candidat,
            'form' => $form->createView()
        ]);
    }

    // #[Route('/apercu_document/{id}', name: 'apercu_document')]
    // public function apercuDocument(Document $document, CandidatRepository $candidatRepository)
    // {
    //     $candidat = $candidatRepository->findOneBy(["email" => $this->getUser()->getEmail()]); 
    //     if ($document->getCandidat() == $candidat) {
    //         //Cette fonction renvoie le fichier de publipostage pour qu'il puissent être téléchargé
    //         $content = file_get_contents($document->getUrl());
    //         $response = new Response();
    //         $response->headers->set('Content-Type', 'mime/type');
    //         $response->headers->set('Content-Disposition', 'attachment;filename="'.$document->getNom());
    //         $response->setContent($content);
    //         return $response;
    //     }else {
    //         return new Response("Vous n'êtes pas autorisé à accéder à cette ressource", Response::HTTP_UNAUTHORIZED); 
    //     }
    // }

    // #[Route('/verification', name: 'app_verification_candidat')]
    // public function verifInfos(Request $request, CandidatRepository $candidatRepository): Response
    // {
    //     $candidat = $candidatRepository->findOneBy(["email" => $this->getUser()->getEmail()]); 

    //     return $this->render('formulaire_candidat/verif.html.twig', [
    //         'candidat' => $candidat
    //     ]);
    // }
}
