<?php

namespace App\Controller;

use App\Entity\Candidat;
use App\Entity\TypeCandidat;
use App\Form\TypeCandidatType;
use App\Repository\TypeCandidatRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/type/candidat')]
class TypeCandidatController extends AbstractController
{
    #[Route('/', name: 'app_type_candidat_index', methods: ['GET'])]
    public function index(TypeCandidatRepository $typeCandidatRepository): Response
    {
        return $this->render('type_candidat/index.html.twig', [
            'type_candidats' => $typeCandidatRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_type_candidat_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeCandidatRepository $typeCandidatRepository): Response
    {
        $typeCandidat = new TypeCandidat();
        $form = $this->createForm(TypeCandidatType::class, $typeCandidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeCandidatRepository->add($typeCandidat, true);
            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_type_candidat_index', [], Response::HTTP_SEE_OTHER);
            } else {
                return $this->redirectToRoute('app_type_candidat_new', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('type_candidat/new.html.twig', [
            'type_candidat' => $typeCandidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_type_candidat_show', methods: ['GET'])]
    public function show(TypeCandidat $typeCandidat): Response
    {
        return $this->render('type_candidat/show.html.twig', [
            'type_candidat' => $typeCandidat,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_type_candidat_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeCandidat $typeCandidat, TypeCandidatRepository $typeCandidatRepository): Response
    {
        $form = $this->createForm(TypeCandidatType::class, $typeCandidat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeCandidatRepository->add($typeCandidat, true);
            if ($form->get("valider")->isClicked()) {
                return $this->redirectToRoute('app_type_candidat_index', [], Response::HTTP_SEE_OTHER);
            } else {
                $id = $typeCandidat->getId() == $typeCandidatRepository->findMaxId() ? $typeCandidatRepository->findMinId() : $typeCandidatRepository->findIdSuivant($typeCandidat->getId()); 
                return $this->redirectToRoute('app_type_candidat_edit', ["id" => $id], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->renderForm('type_candidat/edit.html.twig', [
            'type_candidat' => $typeCandidat,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_type_candidat_delete', methods: ['POST'])]
    public function delete(Request $request, TypeCandidat $typeCandidat, TypeCandidatRepository $typeCandidatRepository): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete'.$typeCandidat->getId(), $request->request->get('_token'))) {
                $typeCandidatRepository->remove($typeCandidat, true);
            }
            return $this->redirectToRoute('app_type_candidat_index', [], Response::HTTP_SEE_OTHER);
        }catch (ForeignKeyConstraintViolationException $exception) { 
            $candidats = []; 
            foreach ($typeCandidat->getCandidats() as $candidat) {
                $candidats[] = $candidat->getPrenom() . " " . $candidat->getNom();
            }
            $this->addFlash("error", "Erreur dans la suppression du type de contrat un ou des candidats ont ce type de contrat : " . implode(', ',$candidats) , 500);
            //Permet de ne pas changer de page et revenir sur la page sur laquelle nous étions
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }
        catch(\Exception $e){
            $this->addFlash("error","Erreur dans la suppression des types de contrats : " . $e->getMessage(), 500);
            //Permet de ne pas changer de page et revenir sur la page sur laquelle nous étions
            $referer = $request->headers->get('referer');
            return $this->redirect($referer);
        }


    }

    #[Route('/delete_list', name: 'app_type_candidat_list_delete', methods: ['POST'])]
    public function supprimeList(Request $request, EntityManagerInterface $entityManager, TypeCandidatRepository $typeCandidatRepository): Response
    {
        try {
            $liste =  json_decode($request->getContent())->liste; 
            if (in_array("tout", $liste)) {
                foreach ($typeCandidatRepository->findAll() as $typeCandidat) {
                    $typeCandidatRepository->remove($typeCandidat); 
                }
            } else {
                foreach ($liste as $id) {
                    $typeCandidat = $typeCandidatRepository->findOneBy(["id" => intval($id)]);
                    $typeCandidatRepository->remove($typeCandidat); 
                }
            }

            $entityManager->flush(); 
    
            return new Response("Ok", 200);
        }catch (ForeignKeyConstraintViolationException $exception) {
            return new Response("Erreur dans la suppression des types de contrats : certains d'entre eux sont liés à des candidat", 500);
        }
        catch(\Exception $e){
            throw new \Exception("Erreur dans la suppression des types de contrats : " . $e->getMessage(), 500);
        }

    }
}
