<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Document;
use App\Repository\DocumentRepository;
use App\Repository\TypeDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Outils\Slugger; 

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
    private $slugger; 

    public function __construct( $fichiersBack, $fichiersChemin, TypeDocumentRepository $typeDocumentRepository, DocumentRepository $documentRepository, EntityManagerInterface $em)
    {
        //Déclare un filesystem qui permet de créer des répertoires dans notre arborescence de fichiers
        $this->filesystem = new Filesystem();
        //Récupère le chemin des fichiers qui se trouve dans config/services.yaml
        $this->fichiersChemin = $fichiersChemin;
        $this->fichiersBack = $fichiersBack;
        $this->documentRepository = $documentRepository;
        $this->em = $em;
        $this->typeDocumentRepository = $typeDocumentRepository;
        $this->slugger = new Slugger(); 
    }

    public function test(){
        $this->filesystem->mkdir($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' . '0.EMBAUCHE');
    }

    /** Fait une boucle sur tous les champs du formulaire 
     * et pour chaque élément de type UploadedFile ou CollectionType
     * il enregistre en base de donnée le fichier et le créer dans l'arborescence
     */
    public function enregistrerTout(Candidat $candidat, array $fichiers)
    {
        //entités que nous allons renvoyer au controller pour qu'il puisse les persist
        $entites = [];
        // Creer un répertoire pour le candidat afin d'y placer les fichiers
        $this->filesystem->mkdir($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' . '0.EMBAUCHE');
        //En paramètre nous avons reçu un array avec tout les fichiers
        //Pour chaque élement de l'array
        foreach ($fichiers as $fichier) {
            //Si c'est un simple UploadedFile (si l'élément contient uniquement un seul fichier)
            if ('object' == gettype($fichier->getData()) && "Symfony\Component\HttpFoundation\File\UploadedFile" == get_class($fichier->getData())) {
                $type_documents = $this->typeDocumentRepository->findAll();
                foreach ($type_documents as $type_document) {
                    //Si le libelle contiens des espaces on les remplace par des _ car cela renvoi une erreur
                    $libelle = $this->slugger->toSlug($type_document->getLibelle());
                    if ($libelle == $fichier->getName()) {
                        $nomTypeFichier = $type_document->getLibelle();
                    }
                }
                $donneeFichier = $fichier->getData();
                //donne un nouveau nom au fichier de la forme suivante NomPrenom_typeFichier.pdf
                $nomNouveauDocument = strval($candidat->getId()).'_'.$nomTypeFichier.'.'.$donneeFichier->guessExtension();
                //Déclare un nouveau document et lui assigne ses attributs
                $document = new Document();
                $document->setNom($nomNouveauDocument);
                $document->setUrl($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' . '0.EMBAUCHE'.'/'.$document->getNom());
                $document->setExtension($donneeFichier->guessExtension());
                $document->setTaille($donneeFichier->getSize());
                //Récupère le type de fichier dans la base de donné
                $typeFichier = $this->typeDocumentRepository->findOneByLibelle($nomTypeFichier);
                //assigne son type au document
                $document->addType($typeFichier);
                //Ajoute le document courant parmis les types
                $typeFichier->addDocument($document);
                //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                $candidat->addDocument($document);
                //Ajoute le document dans le tableau des entites modifiés
                $entites[] = $document;
                //Ajoute le type de fichier dans les entites modifiés
                $entites[] = $typeFichier;
                //met le fichier dans le bon répertoire
                $donneeFichier->move(
                    $this->fichiersChemin.'/'.strval($candidat->getId()). '/' . '0.EMBAUCHE',
                    $nomNouveauDocument
                );
            //Dans l'autre cas l'élément courant contient un Collection type (un élément du formulaire pour lequel nous avons plusieurs fichiers)
            } elseif ('array' == gettype($fichier->getData()) && !empty($fichier->getData())) {
                $type_documents = $this->typeDocumentRepository->findAll();
                foreach ($type_documents as $type_document) {
                    //Si le libelle contiens des espaces on les remplace par des _ car cela renvoi une erreur
                    $libelle = $this->slugger->toSlug($type_document->getLibelle());
                    if ($libelle == $fichier->getName()) {
                        $nomTypeFichier = $type_document->getLibelle();
                    }
                }
                //Récupère le type de fichier dans la base de donnée
                $typeFichier = $this->typeDocumentRepository->findOneByLibelle($nomTypeFichier);
                $numeroFichier = 1;
                //Pour chaque fichiers qui sont ici tous de même type.
                foreach ($fichier as $fichierCollection) {
                    //récupère les données (le fichier)
                    $donneeFichier = $fichierCollection->getData();
                    if (null != $donneeFichier) {
                        //donne un nouveau nom au fichier
                        $nomNouveauDocument = strval($candidat->getId()).'_'.$nomTypeFichier.'_'.$numeroFichier.'.'.$donneeFichier->guessExtension();
                        //Déclare un nouveau document et lui assigne ses attributs
                        $document = new Document();
                        $document->setNom($nomNouveauDocument);
                        $document->setUrl($this->fichiersChemin.'/'.strval($candidat->getId()) . '/' . '0.EMBAUCHE'.'/'.$document->getNom());
                        $document->setExtension($donneeFichier->guessExtension());
                        $document->setTaille($donneeFichier->getSize());
                        //assigne son type au document
                        $document->addType($typeFichier);
                        //Ajoute le document courant parmis les types
                        $typeFichier->addDocument($document);
                        //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                        $candidat->addDocument($document);
                        //ajoute le document dans le tableau des entites modifiés
                        $entites[] = $document;
                        //met le fichier dans le bon répertoire
                        $donneeFichier->move(
                            $this->fichiersChemin.'/'.strval($candidat->getId()). '/' . '0.EMBAUCHE',
                            $nomNouveauDocument
                        );
                    }
                    ++$numeroFichier;
                }
                //Ajoute le type de fichier dans les entites a retourner
                $entites[] = $typeFichier;
            }
        }

        foreach ($entites as $document) {
            $this->em->persist($document);
        }
        $this->em->flush(); 
    }

    /** Fait pareil que enregistrerTout mais dans enregistre directement dans le répertoire Back
     */
    public function enregistrerToutBack(Candidat $candidat, array $fichiers)
    {
        //entités que nous allons renvoyer au controller pour qu'il puisse les persist
        $entites = [];
        // Creer un répertoire pour le candidat afin d'y placer les fichiers
        // $this->filesystem->mkdir($this->fichiersChemin.'/'.strval($candidat->getId()));
        $this->filesystem->mkdir($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom() . '/' . '0.EMBAUCHE');
        //En paramètre nous avons reçu un array avec tout les fichiers
        //Pour chaque élement de l'array
        foreach ($fichiers as $fichier) {
            //Si c'est un simple UploadedFile (si l'élément contient uniquement un seul fichier)
            if ('object' == gettype($fichier->getData()) && "Symfony\Component\HttpFoundation\File\UploadedFile" == get_class($fichier->getData())) {
                $type_documents = $this->typeDocumentRepository->findAll();
                foreach ($type_documents as $type_document) {
                    //Si le libelle contiens des espaces on les remplace par des _ car cela renvoi une erreur
                    $libelle = $this->slugger->toSlug($type_document->getLibelle());
                    if ($libelle == $fichier->getName()) {
                        $nomTypeFichier = $type_document->getLibelle();
                    }
                }
                $donneeFichier = $fichier->getData();
                //donne un nouveau nom au fichier de la forme suivante NomPrenom_typeFichier.pdf
                $nomNouveauDocument = strtoupper($candidat->getNom()).' '.$candidat->getPrenom().'_'.$nomTypeFichier.'.'.$donneeFichier->guessExtension();
                //Déclare un nouveau document et lui assigne ses attributs
                $document = new Document();
                $document->setNom($nomNouveauDocument);
                $document->setUrl($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom() . '/' . '0.EMBAUCHE'.'/'.$document->getNom());
                $document->setExtension($donneeFichier->guessExtension());
                $document->setTaille($donneeFichier->getSize());
                //Récupère le type de fichier dans la base de donné
                $typeFichier = $this->typeDocumentRepository->findOneByLibelle($nomTypeFichier);
                //assigne son type au document
                $document->addType($typeFichier);
                //Ajoute le document courant parmis les types
                $typeFichier->addDocument($document);
                //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                $candidat->addDocument($document);
                //Ajoute le document dans le tableau des entites modifiés
                $entites[] = $document;
                //Ajoute le type de fichier dans les entites modifiés
                $entites[] = $typeFichier;
                //met le fichier dans le bon répertoire
                $donneeFichier->move(
                    $this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom(). '/' . '0.EMBAUCHE',
                    $nomNouveauDocument
                );
            //Dans l'autre cas l'élément courant contient un Collection type (un élément du formulaire pour lequel nous avons plusieurs fichiers)
            } elseif ('array' == gettype($fichier->getData()) && !empty($fichier->getData())) {
                $type_documents = $this->typeDocumentRepository->findAll();
                foreach ($type_documents as $type_document) {
                    //Si le libelle contiens des espaces on les remplace par des _ car cela renvoi une erreur
                    $libelle = $this->slugger->toSlug($type_document->getLibelle());
                    if ($libelle == $fichier->getName()) {
                        $nomTypeFichier = $type_document->getLibelle();
                    }
                }
                //Récupère le type de fichier dans la base de donnée
                $typeFichier = $this->typeDocumentRepository->findOneByLibelle($nomTypeFichier);
                $numeroFichier = 1;
                //Pour chaque fichiers qui sont ici tous de même type.
                foreach ($fichier as $fichierCollection) {
                    //récupère les données (le fichier)
                    $donneeFichier = $fichierCollection->getData();
                    if (null != $donneeFichier) {
                        //donne un nouveau nom au fichier
                        $nomNouveauDocument = strtoupper($candidat->getNom()).' '.$candidat->getPrenom().'_'.$nomTypeFichier.'_'.$numeroFichier.'.'.$donneeFichier->guessExtension();
                        //Déclare un nouveau document et lui assigne ses attributs
                        $document = new Document();
                        $document->setNom($nomNouveauDocument);
                        $document->setUrl($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom() . '/' . '0.EMBAUCHE'.'/'.$document->getNom());
                        $document->setExtension($donneeFichier->guessExtension());
                        $document->setTaille($donneeFichier->getSize());
                        //assigne son type au document
                        $document->addType($typeFichier);
                        //Ajoute le document courant parmis les types
                        $typeFichier->addDocument($document);
                        //ajoute ce document au candidat et document->setCandidat() est fait dans la methode addDocument
                        $candidat->addDocument($document);
                        //ajoute le document dans le tableau des entites modifiés
                        $entites[] = $document;
                        //met le fichier dans le bon répertoire
                        $donneeFichier->move(
                            $this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom(). '/' . '0.EMBAUCHE',
                            $nomNouveauDocument
                        );
                    }
                    ++$numeroFichier;
                }
                //Ajoute le type de fichier dans les entites a retourner
                $entites[] = $typeFichier;
            }
        }

        foreach ($entites as $document) {
            $this->em->persist($document);
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
    /**Supprime tous les fichiers d'un candidat dans le répertoire final
     * en BDD et dans l'arborescence*/
    public function supprimerBack(Candidat $candidat)
    {
        try {
            $documents = $this->documentRepository->findByCandidatId($candidat);
            foreach ($documents as $document) {
                $this->em->remove($document);
            }
            $this->em->flush();
        } catch (\Exception $exception) {
            throw new \Exception('Document base de données');
        }
        try {
            $this->filesystem->remove($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom());
        } catch (\Exception $exception) {
            throw new \Exception('fichiers');
        }
    }

    /**Supprime un seul document */
    public function supprimer(Document $document)
    {
        try {
            if($this->filesystem->exists($this->fichiersChemin.'/'.strval($document->getCandidat()->getId()). '/' . '0.EMBAUCHE'.'/'.$document->getNom())){
                $this->filesystem->remove($this->fichiersChemin.'/'.strval($document->getCandidat()->getId()). '/' . '0.EMBAUCHE'.'/'.$document->getNom());
            }else if($this->filesystem->exists($this->fichiersBack.'/'.strval($document->getCandidat()->getId()). '/' . '0.EMBAUCHE'.'/'.$document->getNom())) {
                $this->filesystem->remove($this->fichiersBack.'/'.strval($document->getCandidat()->getId()). '/' . '0.EMBAUCHE'.'/'.$document->getNom());
            }else {
                return false; 
            }
            $this->em->remove($document);
            $this->em->flush();  
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '.$exception->getPath();
        }
    }

    /**Déplace les fichiers du répertoire initial au répertoire final */
    public function deplacerFichiers(Candidat $candidat)
    {
        try {
            $this->filesystem->mkdir($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom(). '/' . '0.EMBAUCHE');
            //mirror permet de faire une copie de fichier
            $this->filesystem->mirror($this->fichiersChemin.'/'.strval($candidat->getId()). '/' . '0.EMBAUCHE', $this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom(). '/' . '0.EMBAUCHE');
            if ($this->filesystem->exists($this->fichiersChemin.'/'.strval($candidat->getId()))) {
                $this->filesystem->remove($this->fichiersChemin.'/'.strval($candidat->getId()));
            }
            foreach($candidat->getDocuments() as $document){
                $document->setUrl($this->fichiersBack.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom(). '/' . '0.EMBAUCHE' . '/' . $document->getNom()); 
            }
        } catch (IOExceptionInterface $exception) {
            echo 'An error occurred while creating your directory at '.$exception->getPath();
        }
    }
}
