<?php

namespace App\Controller;

use App\Entity\MailTemplate;
use App\Exception\InvalidImageMailTemplateException;
use App\Form\AddImageMailTemplateType;
use App\Form\MailTemplateType;
use App\Repository\MailTemplateRepository;
use App\Service\MailTemplateService;
use Exception;
use Flasher\Toastr\Prime\ToastrFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request ;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'app_administration', methods: ['GET', 'POST'])]
    public function index(Request $request, MailTemplateRepository $mailTemplateRepository, ToastrFactory $toastrFactory, MailTemplateService $mailTemplateService): Response
    {
        $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  
        $mailform = $this->createForm(MailTemplateType::class, $mailTemplate);
        $mailform->handleRequest($request);

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


        if ($mailform->isSubmitted() && $mailform->isValid()) {
            $mailTemplateRepository->add($mailTemplate, true); 
            $toastrFactory->addSuccess("Enregistré avec succès"); 
        }

        $imagesUrl = $mailTemplateService->getAllImagesUrl(); 

        return $this->render('administration/index.html.twig', [
            'mailForm' => $mailform->createView(),
            'imageForm' => $imageForm->createView(), 
            'imagesUrl' => $imagesUrl
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
}
