<?php

namespace App\Controller;

use App\Entity\TypeDocument;
use App\Form\TypeDocumentType;
use App\Repository\TypeDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/type/document')]
class TypeDocumentController extends AbstractController
{
    #[Route('/', name: 'app_type_document_index', methods: ['GET'])]
    public function index(TypeDocumentRepository $typeDocumentRepository): Response
    {
        return $this->render('type_document/index.html.twig', [
            'type_documents' => $typeDocumentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeDocumentRepository $typeDocumentRepository): Response
    {
        $typeDocument = new TypeDocument();
        $form = $this->createForm(TypeDocumentType::class, $typeDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeDocumentRepository->add($typeDocument, true);
            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_type_document_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_type_document_new', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('type_document/new.html.twig', [
            'type_document' => $typeDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_document_show', methods: ['GET'])]
    public function show(TypeDocument $typeDocument): Response
    {
        return $this->render('type_document/show.html.twig', [
            'type_document' => $typeDocument,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeDocument $typeDocument, TypeDocumentRepository $typeDocumentRepository): Response
    {
        $form = $this->createForm(TypeDocumentType::class, $typeDocument);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeDocumentRepository->add($typeDocument,true);  

            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_type_document_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $id = $typeDocument->getId() == $typeDocumentRepository->findMaxId() ? $typeDocumentRepository->findMinId() : $typeDocumentRepository->findIdSuivant($typeDocument->getId()); 
                return $this->redirectToRoute('app_type_document_edit', ["id" => $id], Response::HTTP_SEE_OTHER);
            }
    
        }

        return $this->renderForm('type_document/edit.html.twig', [
            'type_document' => $typeDocument,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_type_document_delete', methods: ['POST'])]
    public function delete(Request $request, TypeDocument $typeDocument, TypeDocumentRepository $typeDocumentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeDocument->getId(), $request->request->get('_token'))) {
            $typeDocumentRepository->remove($typeDocument, true);
        }

        return $this->redirectToRoute('app_type_document_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete_list', name: 'app_type_document_list_delete', methods: ['POST'])]
    public function supprimeList(Request $request, EntityManagerInterface $entityManager, TypeDocumentRepository $typeDocumentRepository): Response
    {
        try {
            $liste =  json_decode($request->getContent())->liste; 
            if (in_array("tout", $liste)) {
                foreach ($typeDocumentRepository->findAll() as $typeDocument) {
                    $typeDocumentRepository->remove($typeDocument); 
                }
            } else {
                foreach ($liste as $id) {
                    $typeDocumentRepository->remove($typeDocumentRepository->findOneBy(["id" => intval($id)])); 
                }
            }
            $entityManager->flush();
    
            return new Response("Ok", 200);
        } catch (\Exception $e) {
            return new Response("Erreur dans la suppression des types de documents", 500);
        }

    }
}
