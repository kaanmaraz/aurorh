<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\MailTemplate;
use App\Exception\EnvoiMailException;
use App\Exception\InvalidImageMailTemplateException;
use App\Form\AddImageMailTemplateType;
use App\Form\CandidatType;
use App\Form\MailTemplateType;
use App\Repository\CandidatRepository;
use App\Repository\MailTemplateRepository;
use App\Service\MailTemplateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/candidat')]
class CandidatController extends AbstractController
{

    private $candidatWorkflow; 

    public function __construct(WorkflowInterface $candidatWorkflow)
    {
        $this->candidatWorkflow = $candidatWorkflow; 
    }

    #[Route('/', name: 'app_candidat_index', methods: ['GET', 'POST'])]
    public function index(Request $request, CandidatRepository $candidatRepository,  MailTemplateRepository $mailTemplateRepository,MailTemplateService $mailTemplateService): Response
    {
        $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  
        $mailform = $this->createForm(MailTemplateType::class, $mailTemplate);
        $mailform->handleRequest($request);

        if ($mailform->isSubmitted()) {
            $mailTemplateRepository->add($mailTemplate, true); 
            return $this->json($mailTemplate); 
        }

        $imageForm = $this->createForm(AddImageMailTemplateType::class);
        $imageForm->handleRequest($request);
        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            /** @var UploadedFile $image  */ 
            $image = $imageForm->get("image")->getData(); 
            try {
                $urlImage = $mailTemplateService->enregistreImageTemplate($image); 
                return new Response($urlImage, Response::HTTP_OK); 
            } catch (InvalidImageMailTemplateException $imageInvalideException) {
                return new Response($imageInvalideException->getMessage(),Response::HTTP_NOT_ACCEPTABLE); 
            }
        }elseif ($imageForm->isSubmitted() && !$imageForm->isValid()) {
            return new Response("Erreur dans l'enregistrement de l'image veuillez la télécharger au bon format",Response::HTTP_NOT_ACCEPTABLE); 
        }
        $imagesUrl = $mailTemplateService->getAllImagesUrl(); 

        return $this->render('candidat/index.html.twig', [
            'candidats' => $candidatRepository->findAll(),
            'mailForm' => $mailform->createView(), 
            'imageForm' => $imageForm->createView(), 
            'imagesUrl' => $imagesUrl
        ]);
    }

    #[Route('/new', name: 'app_candidat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CandidatRepository $candidatRepository): Response
    {
        $candidat = new Candidat();
        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->candidatWorkflow->apply($candidat, 'to_attente_envoi_mail_form'); 
            $candidatRepository->add($candidat, true);

            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_candidat_new', [], Response::HTTP_SEE_OTHER);
            }
            
        }

        return $this->renderForm('candidat/new.html.twig', [
            'candidat' => $candidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/show', name: 'app_candidat_show', methods: ['GET'])]
    public function show(Candidat $candidat): Response
    {
        return $this->render('candidat/show.html.twig', [
            'candidat' => $candidat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidat $candidat, CandidatRepository $candidatRepository): Response
    {
        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidatRepository->add($candidat, true);

            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $id = $candidat->getId() == $candidatRepository->findMaxId() ? $candidatRepository->findMinId() : $candidatRepository->findIdSuivant($candidat->getId()); 
                return $this->redirectToRoute('app_candidat_edit', ["id" => $id], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('candidat/edit.html.twig', [
            'candidat' => $candidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_candidat_delete', methods: ['POST'])]
    public function delete(Request $request, Candidat $candidat, CandidatRepository $candidatRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidat->getId(), $request->request->get('_token'))) {
            $candidatRepository->remove($candidat, true);
        }

        return $this->redirectToRoute('app_candidat_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete_list', name: 'app_candidat_list_delete', methods: ['POST'])]
    public function supprimeList(Request $request, EntityManagerInterface $entityManager, CandidatRepository $candidatRepository): Response
    {
        try {
            $liste =  json_decode($request->getContent())->liste; 
            if (in_array("tout", $liste)) {
                foreach ($candidatRepository->findAll() as $candidat) {
                    $candidatRepository->remove($candidat); 
                }
            } else {
                foreach ($liste as $id) {
                    $candidatRepository->remove($candidatRepository->findOneBy(["id" => intval($id)])); 
                }
            }
            $entityManager->flush();
    
            return new Response("Ok", 200);
        } catch (\Exception $e) {
            return new Response("Erreur dans la suppression des candidats", 500);
        }
    }

    #[Route("/get_mail_template/{id}", name:"get_mail_template")]
    public function getMailTemplateCandidat(Request $request,Candidat $candidat, MailTemplateService $mailTemplateService, MailTemplateRepository $mailTemplateRepository): Response
    {
        if (count($mailTemplateRepository->findAll()) !== 0) {
            $mailTemplate = $mailTemplateRepository->findAll()[0]; 
        }
        try {
            $contenuMail = $mailTemplateService->getMailTemplateCandidat($mailTemplate, $candidat); 
            $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  
            $mailTemplate->setContenu($contenuMail); 
            
            
            // Le formulaire AJAX est géré ici 
            $mailform = $this->createForm(MailTemplateType::class, $mailTemplate);
            $mailform->handleRequest($request);

            if ($mailform->isSubmitted()) {
                $mailTemplateRepository->add($mailTemplate, true); 
                // Je retourne un json qui sera contenu dans la variable 'response' de la réponse
                return $this->json($mailTemplate); 
            }




            $imageForm = $this->createForm(AddImageMailTemplateType::class);
            $imageForm->handleRequest($request);
            if ($imageForm->isSubmitted() && $imageForm->isValid()) {
                /** @var UploadedFile $image  */ 
                $image = $imageForm->get("image")->getData(); 
                try {
                    $urlImage = $mailTemplateService->enregistreImageTemplate($image); 
                    return new Response($urlImage, Response::HTTP_OK); 
                } catch (InvalidImageMailTemplateException $imageInvalideException) {
                    return new Response($imageInvalideException->getMessage(),Response::HTTP_NOT_ACCEPTABLE); 
                }
            }elseif ($imageForm->isSubmitted() && !$imageForm->isValid()) {
                return new Response("Erreur dans l'enregistrement de l'image veuillez la télécharger au bon format",Response::HTTP_NOT_ACCEPTABLE); 
            }
            $imagesUrl = $mailTemplateService->getAllImagesUrl(); 

            return $this->render('_form_mail_template.html.twig', [
                'mailForm' => $mailform->createView(), 
                'imageForm' => $imageForm->createView(), 
                'imagesUrl' => $imagesUrl
            ]); 
        } catch (EnvoiMailException $envoiMailException) {
            return new Response("Erreur ! certains attribut du candidat sont nuls veuillez les renseigner : " . $envoiMailException->getAttributsManquants(), Response::HTTP_NOT_ACCEPTABLE); 
        }
        
    }

    #[Route("/envoi_lien/{id}",name:"candidat_envoi_lien")]
    public function envoiLien(): Response
    {
        return $this->render('$0.html.twig', []);
    }
}
