<?php

namespace App\Controller;

use App\Entity\MailTemplate;
use App\Entity\PieceJointe;
use App\Exception\InvalidFileMailTemplateException;
use App\Exception\InvalidImageMailTemplateException;
use App\Form\AddImageMailTemplateType;
use App\Form\AddPJMailTemplateType;
use App\Form\MailTemplateType;
use App\Repository\MailTemplateRepository;
use App\Repository\PieceJointeRepository;
use App\Service\MailTemplateService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Flasher\Toastr\Prime\ToastrFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration', methods: ['GET', 'POST'])]
    public function index(Request $request, 
                            MailTemplateRepository $mailTemplateRepository, 
                            ToastrFactory $toastrFactory, 
                            MailTemplateService $mailTemplateService, 
                            EntityManagerInterface $entityManager,
                            SerializerInterface $serializer): Response
    {
        /** @var MailTemplate $mailTemplate */
        $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  
        $mailform = $this->createForm(MailTemplateType::class, $mailTemplate);
        $mailform->handleRequest($request);

        $imageForm = $this->createForm(AddImageMailTemplateType::class);
        $imageForm->handleRequest($request);

        $pjForm = $this->createForm(AddPJMailTemplateType::class);
        $pjForm->handleRequest($request);


        if ($mailform->isSubmitted() && $mailform->isValid()) {
            $mailTemplateRepository->add($mailTemplate, true); 
            $toastrFactory->addSuccess("Enregistré avec succès"); 
        }


        if ($imageForm->isSubmitted() && $imageForm->isValid()) {
            /** @var UploadedFile $image  */ 
            $image = $imageForm->get("image")->getData(); 
            try {
                $urlImage = $mailTemplateService->enregistreImageTemplate($image); 
                return new Response($urlImage, Response::HTTP_OK); 
            } catch (InvalidFileMailTemplateException $imageInvalideException) {
                return new Response($imageInvalideException->getMessage(),Response::HTTP_NOT_ACCEPTABLE); 
            }
        }elseif ($imageForm->isSubmitted() && !$imageForm->isValid()) {
            return new Response("Erreur dans l'enregistrement de l'image veuillez la télécharger au bon format",Response::HTTP_NOT_ACCEPTABLE); 
        }


        if ($pjForm->isSubmitted() && $pjForm->isValid()) {
            /** @var UploadedFile $pj  */ 
            $pj = $pjForm->get("pj")->getData(); 
            try {
                /** @var PieceJointe $pieceJointe */
                $pieceJointe =  $mailTemplateService->enregistrePJTemplate($mailTemplate,$pj); 
                $context = (new ObjectNormalizerContextBuilder())
                    ->withGroups('pj_api')
                    ->toArray();
                $pieceJointe = $serializer->serialize($pieceJointe, 'json', $context); 

                return $this->json($pieceJointe); 
            } catch (InvalidFileMailTemplateException $pjInvalideException) {
                return new Response($pjInvalideException->getMessage(),Response::HTTP_NOT_ACCEPTABLE); 
            }
        }elseif ($pjForm->isSubmitted() && !$pjForm->isValid()) {
            return new Response("Erreur dans l'enregistrement du fichier veuillez le télécharger au bon format",Response::HTTP_NOT_ACCEPTABLE); 
        }
        $entityManager->flush(); 

        return $this->render('administration/index.html.twig', [
            'mailForm' => $mailform->createView(),
            'imageForm' => $imageForm->createView(), 
            'pjForm' => $pjForm->createView(), 
            'imagesUrl' => $mailTemplateService->getAllImagesUrl(), 
            'mailTemplate' => $mailTemplate
        ]);
    }

    #[Route('/api_delete_image', name: 'api_delete_image', methods: ['POST'])]
    public function deleteImage(Request $request, MailTemplateService $mailTemplateService)
    {
        try {
            $mailTemplateService->deleteImageByUrl(json_decode($request->getContent())->url);
            return new Response("Fichier supprimé", 200);  
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), 500); 
        }
    }

    #[Route('/api_delete_pj', name: 'api_delete_pj', methods: ['POST'])]
    public function deletePJ(Request $request, MailTemplateService $mailTemplateService)
    {
        try {
            $mailTemplateService->deletePJ(json_decode($request->getContent())->id);
            return new Response("Fichier supprimé", 200);  
        } catch (Exception $exception) {
            return new Response($exception->getMessage(), 500); 
        }
    }

    #[Route('/api_update_pj_actif/{id}', name: 'api_update_pj_actif', methods:['POST'])]
    public function updatePj(Request $request, PieceJointeRepository $pieceJointeRepository, EntityManagerInterface $entityManager, $id): Response
    {
        $pieceJointe = $pieceJointeRepository->findOneBy(["id" => intval($id)]); 
        $pieceJointe->setActif(json_decode($request->getContent())->actif); 
        $entityManager->flush(); 
        return new Response("UPDATED", Response::HTTP_ACCEPTED); 
    }
}
