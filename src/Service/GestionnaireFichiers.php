<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Repository\TypeDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GestionnaireFichiers
{
    private $fichiersChemin;
    private $fichiersBack; 
    /**
     * @var TypeDocumentRepository
     */
    private $typeDocumentRepository;

    /**
     * @var Filesystem
     */
    private $filesystem;
    private $documentRepository;
    /* @var Doctrine\ORM\EntityManagerInterface $em */

    protected $em; 

    public function __construct( $fichiersChemin, TypeDocumentRepository $typeDocumentRepository, DocumentRepository $documentRepository, EntityManagerInterface $em)
    {
        //Déclare un filesystem qui permet de créer des répertoires dans notre arborescence de fichiers
        $this->filesystem = new Filesystem();
        //Récupère le chemin des fichiers qui se trouve dans config/services.yaml
        $this->fichiersChemin = $fichiersChemin;
        $this->documentRepository = $documentRepository;
        $this->em = $em;
        $this->typeDocumentRepository = $typeDocumentRepository;
    }
    /** Fait une boucle sur tous les champs du formulaire 
     * et pour chaque élément de type UploadedFile ou CollectionType
     * il enregistre en base de donnée le fichier et le créer dans l'arborescence
     */
    public function enregistrerTout(Candidat $candidat, array $champs)
    {
        // Creer un répertoire pour le candidat afin d'y placer les fichiers
        $this->filesystem->mkdir($this->fichiersChemin.'/'.strval($candidat->getId()));
        //En paramètre nous avons reçu un array avec tout les fichiers
        //Pour chaque élement de l'array
        foreach ($champs as $champ) { 
            //Si c'est un simple UploadedFile (si l'élément contient uniquement un seul fichier)
            if ('object' == gettype($champ->getData()) && "Symfony\Component\HttpFoundation\File\UploadedFile" == get_class($champ->getData())) {

                $donneeFichier = $champ->getData();
                //donne un nouveau nom au fichier de la forme suivante NomPrenom_typeFichier.pdf
                $nomNouveauDocument = strval($candidat->getId()).'_'.$champ->getName().'.'.$donneeFichier->guessExtension();
                //Déclare un nouveau document et lui assigne ses attributs
                $document = (new Document())
                                ->setNom($nomNouveauDocument)
                                ->setUrl($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' . $nomNouveauDocument)
                                ->setExtension($donneeFichier->guessExtension())
                                ->setTaille($donneeFichier->getSize());

                //Récupère le type de fichier dans la base de donné
                $typeDocument = $this->typeDocumentRepository->findOneBy(["slug" => $champ->getName()]);
                //assigne son type au document
                $document->addType($typeDocument);
                //Ajoute le document courant parmis les types
                $typeDocument->addDocument($document);
                //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                $candidat->addDocument($document);

                $this->em->persist($document); 

                //met le fichier dans le bon répertoire
                $donneeFichier->move(
                    $this->fichiersChemin.'/'.strval($candidat->getId()),
                    $nomNouveauDocument
                );
            //Dans l'autre cas l'élément courant contient un Collection type (un élément du formulaire pour lequel nous avons plusieurs fichiers)
            } elseif ('array' == gettype($champ->getData()) && !empty($champ->getData())) {

                //Récupère le type de fichier dans la base de donnée
                $typeFichier = $this->typeDocumentRepository->findOneBy(["slug" => $champ->getName()]);
                $numeroFichier = 1;
                //Pour chaque fichiers qui sont ici tous de même type.
                foreach ($champ as $fichierCollection) {
                    //récupère les données (le fichier)
                    $donneeFichier = $fichierCollection->getData();
                    if (null != $donneeFichier) {
                        //donne un nouveau nom au fichier
                        $nomNouveauDocument = strval($candidat->getId()).'_'.$champ->getName().'_'.$numeroFichier.'.'.$donneeFichier->guessExtension();
                        //Déclare un nouveau document et lui assigne ses attributs
                        $document = (new Document())
                                        ->setNom($nomNouveauDocument)
                                        ->setUrl($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' .$nomNouveauDocument)
                                        ->setExtension($donneeFichier->guessExtension())
                                        ->setTaille($donneeFichier->getSize());
                        //assigne son type au document
                        $document->addType($typeFichier);
                        //Ajoute le document courant parmis les types
                        $typeFichier->addDocument($document);
                        //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                        $candidat->addDocument($document);
                        
                        $this->em->persist($document);
                        
                        //met le fichier dans le bon répertoire
                        $donneeFichier->move(
                            $this->fichiersChemin.'/'.strval($candidat->getId()),
                            $nomNouveauDocument
                        );
                    }
                    ++$numeroFichier;
                }
            }
        }
        $this->em->flush(); 
    }

    /**Supprime tous les fichiers d'un candidat
     * en BDD et dans l'arborescence*/
    public function supprimerTout(Candidat $candidat)
    {
        try {
            $documents = $this->documentRepository->findByCandidatId($candidat);
            foreach ($documents as $document) {
                $this->em->remove($document);
            }
            $this->em->flush();
            $this->filesystem->remove($this->fichiersChemin.'/'.strval($candidat->getId()));
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '.$exception->getPath();
        }
    }

    /**Supprime un seul document */
    public function supprimer(Document $document)
    {
        try {
            if($this->filesystem->exists($this->fichiersChemin.'/'.strval($document->getCandidat()->getId()). '/' . $document->getNom())){
                $this->filesystem->remove($this->fichiersChemin.'/'.strval($document->getCandidat()->getId()). '/' . $document->getNom());
            }else {
                return false; 
            }
            $this->em->remove($document);
            $this->em->flush();  
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '.$exception->getPath();
        }
    }
}
