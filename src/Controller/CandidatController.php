<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\MailEnvoye;
use App\Entity\MailTemplate;
use App\Entity\User;
use App\Exception\EnvoiMailException;
use App\Exception\InvalidFileMailTemplateException;
use App\Exception\InvalidImageMailTemplateException;
use App\Form\AddImageMailTemplateType;
use App\Form\AddPJMailTemplateType;
use App\Form\CandidatType;
use App\Form\MailTemplateType;
use App\Repository\CandidatRepository;
use App\Repository\MailTemplateRepository;
use App\Repository\UserRepository;
use App\Service\MailTemplateService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/candidat')]
class CandidatController extends AbstractController
{

    private $candidatWorkflow;
    private $entityManager;  

    public function __construct(WorkflowInterface $candidatWorkflow, 
                                EntityManagerInterface $entityManager)
    {
        $this->candidatWorkflow = $candidatWorkflow; 
        $this->entityManager = $entityManager; 
    }

    #[Route('/', name: 'app_candidat_index', methods: ['GET', 'POST'])]
    public function index(Request $request, 
                            CandidatRepository $candidatRepository,  
                            MailTemplateRepository $mailTemplateRepository,
                            MailTemplateService $mailTemplateService, 
                            SerializerInterface $serializer): Response
    {
        $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  
        $mailform = $this->createForm(MailTemplateType::class, $mailTemplate);
        // $mailform->handleRequest($request);

        $pjForm = $this->createForm(AddPJMailTemplateType::class);
        $pjForm->handleRequest($request);

        $imageForm = $this->createForm(AddImageMailTemplateType::class);
        $imageForm->handleRequest($request);

        // if ($mailform->isSubmitted()) {
        //     // $mailTemplateService->envoiLienCandidat()
        //     return $this->json($mailTemplate); 
        // }

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
        $imagesUrl = $mailTemplateService->getAllImagesUrl(); 

        return $this->render('candidat/index.html.twig', [
            'candidats' => $candidatRepository->findAll(),
            'mailForm' => $mailform->createView(), 
            'imageForm' => $imageForm->createView(), 
            'imagesUrl' => $imagesUrl
        ]);
    }

    #[Route('/new', name: 'app_candidat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CandidatRepository $candidatRepository, UserRepository $userRepository, UserPasswordHasherInterface $hasher): Response
    {
        $candidat = new Candidat();
        $form = $this->createForm(CandidatType::class, $candidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $this->candidatWorkflow->apply($candidat, 'to_attente_envoi_mail_form'); 
            
            $user = new User(); 
            $user->setEmail($candidat->getEmail()); 
            $password = $hasher->hashPassword($user, $user->getMdpGenere() );
            $user->setPassword($password);
            $user->setRoles(["ROLE_CANDIDAT"]);

            $candidatRepository->add($candidat, true);
            $userRepository->add($user, true);

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

    #[Route("/mail_template/{id}", name:"mail_template")]
    public function getMailTemplateCandidat(Request $request,
                                            Candidat $candidat, 
                                            MailTemplateService $mailTemplateService, 
                                            MailTemplateRepository $mailTemplateRepository, 
                                            LoginLinkHandlerInterface $loginLinkHandler, 
                                            MailerInterface $mailer, 
                                            UserRepository $userRepository): Response
    {

        try {
            $mailTemplate = $mailTemplateRepository->findAll()[0] ?: new MailTemplate();  

            $mailAEnvoyer = new MailEnvoye(); 
            $contenuMail = $mailTemplateService->getMailTemplateCandidat($mailTemplate, $candidat); 
            $mailAEnvoyer->setSujet($mailTemplate->getSujet()); 
            $mailAEnvoyer->setContenu($contenuMail); 
            
            // Le formulaire AJAX est géré ici 
            $mailForm = $this->createForm(MailTemplateType::class, $mailAEnvoyer);
            $mailForm->handleRequest($request);

            $imageForm = $this->createForm(AddImageMailTemplateType::class);
            $imageForm->handleRequest($request);

            $pjForm = $this->createForm(AddPJMailTemplateType::class);
            $pjForm->handleRequest($request);

            if ($mailForm->isSubmitted() && $mailForm->isValid()) {
                $mailTemplateService->envoiLienCandidat($candidat,$mailTemplate,$mailForm);
                return new Response("Mail envoyé avec succès", 200);  
            }

            $imagesUrl = $mailTemplateService->getAllImagesUrl(); 

            return $this->render('_partials/_form_mail_template.html.twig', [
                'mailForm' => $mailForm->createView(), 
                'imageForm' => $imageForm->createView(), 
                'pjForm' => $pjForm->createView(), 
                'imagesUrl' => $imagesUrl, 
                'mailTemplate' => $mailTemplate
            ]); 
        } catch (EnvoiMailException $envoiMailException) {
            return new Response("Erreur ! certains attribut du candidat sont nuls veuillez les renseigner : " . $envoiMailException->getAttributsManquants(), Response::HTTP_NOT_ACCEPTABLE); 
        }
        
    }
}
