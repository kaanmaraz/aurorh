<?php

namespace App\Service;

use App\Entity\Candidat;
use App\Entity\Document;
use App\Entity\Lien;
use App\Entity\TypeCandidat;
use App\Entity\TypeDocument;
use App\Repository\CandidatRepository;
use App\Repository\DocumentRepository;
use App\Repository\LienRepository;
use App\Repository\TypeCandidatRepository;
use App\Repository\TypeDocumentRepository;
use App\Repository\UserRepository;
use DateTimeZone;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as Symmetric;
use ParagonIE\Halite\Symmetric\EncryptionKey; 
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Outils\MonSerializeur; 
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Cnam\AdnBundle\Service\AdnService;
use \Exception; 

class WebService
{
    /* @var Doctrine\ORM\EntityManagerInterface $em */
    protected $em;
    private $serializer;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var CandidatRepository
     */
    private $candidatRepository;
    /**
     * @var DocumentRepository
     */
    private $documentRepository;
    /**
     * @var TypeDocumentRepository
     */
    private $typeDocumentRepository;
    /**
     * @var TypeCandidatRepository
     */
    private $typeCandidatRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    private $fichiersChemin;
    private $normalizer;
    private $lienRepository;
    private $logsErreur;
    private $motdepasseAPI;
    private $encrypt;
    private $decrypt;
    private $cipher;
    private $mailAPI;
    private $fichiersBack; 
    private $client; 
    private $monserializeur;
    private $session;
    private $apiAdditionnalPassword; 
    private $jetonGDMAP;
    private $numeroSiret; 
    private $adnService;

    public function __construct( SessionInterface $session, 
                                MonSerializeur $monserializeur, 
                                HttpClientInterface $client,   
                                LogsErreurs $logsErreur, 
                                $fichiersBack,$mailAPI, $motdepasseAPI, $fichiersChemin, $apiAdditionnalPassword, $jetonGDMAP,$numeroSiret,$urlGDMAP,$urlAPIOVH,$urlPROXY,
                                UserRepository $userRepository, 
                                LienRepository $lienRepository, 
                                TypeDocumentRepository $typeDocumentRepository, 
                                TypeCandidatRepository $typeCandidatRepository, 
                                DocumentRepository $documentRepository, 
                                NormalizerInterface $normalizer,  
                                Filesystem $filesystem, 
                                CandidatRepository $candidatRepository, 
                                EntityManagerInterface $em, 
                                SerializerInterface $serializer,
                                AdnService $adnService)
    {
        $this->documentRepository = $documentRepository;
        $this->fichiersChemin = $fichiersChemin;
        $this->filesystem = $filesystem;
        $this->candidatRepository = $candidatRepository;
        $this->typeCandidatRepository = $typeCandidatRepository;
        $this->typeDocumentRepository = $typeDocumentRepository;
        $this->em = $em;
        $this->serializer = $serializer;
        $this->normalizer = $normalizer;
        $this->lienRepository = $lienRepository;
        $this->userRepository = $userRepository;
        $this->motdepasseAPI = $motdepasseAPI;
        $this->mailAPI = $mailAPI;
        $this->logsErreur = $logsErreur;
        $this->fichiersBack =  $fichiersBack;
        $this->client = $client; 
        $this->monserializeur = $monserializeur; 
        $this->session = $session;
        $this->apiAdditionnalPassword = $apiAdditionnalPassword; 
        $this->jetonGDMAP = $jetonGDMAP; 
        $this->numeroSiret = $numeroSiret; 
        $this->adnService = $adnService;
        $this->urlGDMAP = $urlGDMAP; 
        $this->urlAPIOVH = $urlAPIOVH;
        $this->urlPROXY = $urlPROXY; 
    }
	
	public function test(Request $request){
        var_dump($request); 
        var_dump(base64_encode($this->decrypt) ); 
    }

    public function testAPIGDMAP()
    {
        try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_site', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],

            ]);
            return "Liste des sites : " . $response->getContent() ;
        } catch (\Exception $e) {
            return $e->getMessage();
        }

    }

    public function checkNumeroAgent($numero)
    {
        try {
            $agent = $this->adnService->findAllAgentsBy(['idAgent' => $numero . $this->numeroSiret]);
            while(!empty($agent)){
                $numero = '0'. strval(intval($numero)+1); 
                $agent = $this->adnService->findAllAgentsBy(['idAgent' => $numero . $this->numeroSiret]);
            }
            return false; 
        } catch (\Throwable $th) {
            return $numero; 
        }
    }

    public function testNumeroAgent($numero)
    {
        try {
            $agent = $this->adnService->findAllAgentsBy(['idAgent' => $numero . $this->numeroSiret]);
            $reponse = $agent[0]->getNomComplet() . " TEST REUSSI"; 
            return $reponse; 
        } catch (\Exception $e) {
            $reponse =  "ECHEC DU TEST" . $e->getMessage();
            return $reponse;
        }
    }


    public function recupListeTypeMouvementGDMAP()
    {
        try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_type_mouvement', [
            'headers' => [
                'Content-Type' => 'application/json',
                "X-AUTH-TOKEN" => $this->jetonGDMAP
            ],

            ]);

            return $response->getContent(); 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des mouvements sur GDMAP"); 
            return []; 
        }
    }

    public function recupListeServicesGDMAP()
    {
        try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_service', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],

            ]);

            $tableau = []; 
            $jsonArray = json_decode($response->getContent()) ;
            foreach ($jsonArray as $value) {
                $tableau[$value->sigle] =  $value->id; 
            }

            return $tableau; 

            return $response->getContent(); 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des services sur GDMAP"); 
            return []; 
        }

    }

    public function recupListeSitesGDMAP()
    {
        try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_site', [
            'headers' => [
                'Content-Type' => 'application/json',
                "X-AUTH-TOKEN" => $this->jetonGDMAP
            ],

            ]);
            $tableau = []; 
            $jsonArray = json_decode($response->getContent()) ;
            foreach ($jsonArray as $value) {
                $tableau[ $value->libelle] = $value->id; 
            }

            return $tableau; 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des sites sur GDMAP"); 
            return []; 
        }

    }

    public function recupListeEmploiGDMAP()
    {
       try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_emploi', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],

            ]);
            $tableau = []; 
            $jsonArray = json_decode($response->getContent()) ;
            foreach ($jsonArray as $value) {
                $tableau[$value->libelle] = $value->libelle; 
            }

            return $tableau; 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des emplois sur GDMAP"); 
            return []; 
        } 

    }

    public function recupListeTypeContratGDMAP()
    {
        try {
            $response = $this->client->request('GET', $this->urlGDMAP . 'liste_type_contrat/72', [
            'headers' => [
                'Content-Type' => 'application/json',
                "X-AUTH-TOKEN" => $this->jetonGDMAP
            ],

            ]);
            return json_decode($response->getContent()) ;
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des types de contrats sur GDMAP"); 
            return []; 
        } 

    }

    public function recupListeTypeNatureGDMAP($typeContrat)
    {

        try {
            $response = $this->client->request('GET', $this->urlGDMAP . "liste_type_nature/72/$typeContrat", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],

            ]);
            $tableau = []; 
            $jsonArray = json_decode($response->getContent()) ;
            foreach ($jsonArray as $value) {
                $tableau[$value->libelle] = $value->id; 
            }

            return $tableau; 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des types de nature sur GDMAP"); 
            return []; 
        } 

    }

    
    public function recupListeReferentielGDMAP($typeContrat, $typeNature)
    {

        try {
           $response = $this->client->request('GET', $this->urlGDMAP . "liste_referentiel/72/$typeContrat/$typeNature", [
            'headers' => [
                'Content-Type' => 'application/json',
                "X-AUTH-TOKEN" => $this->jetonGDMAP
            ],

            ]);
            return $response->getContent(); 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la r??cup??ration des listes des r??f??rentiels sur GDMAP"); 
            return []; 
        }
        
    }


    public function creationMouvement(Candidat $candidat)
    {   
        try {
           $json = "{\"NumeroAgent\":\"". $candidat->getNumeroAgent() ."\",\"NomAgent\":\"". $candidat->getNom() ."\",\"PrenomAgent\":\"". $candidat->getPrenom() ."\",
            \"EmailAgent\": \"". $candidat->getEmail() ."\",\"FonctionAgent\": \"". $candidat->getPoste() ."\",\"_serviceagent\": ". $candidat->getService() .",
            \"_manageragent\":\"". $candidat->getNumeroAgentManager() ."\",\"DatePrevue\": \"". $candidat->getDatePrevisEmbauche()->format('d/m/Y') ."\",\"_typemouvement\": 72,
            \"_typecontrat\": ". $candidat->getTypeCandidat()->getId() .",\"_typenature\": ". $candidat->getTypeNature() .", \"_referentiel\": ". $candidat->getTypeReferentiel() .",
             \"_site\": ". $candidat->getSite() .",\"_agentcreateur\": \"". $candidat->getAgentCreateur() ."\"}"; 

            $json = json_decode($json); 
            $response = $this->client->request('POST', $this->urlGDMAP . "creation_mouvement", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],
                'json' => $json

            ]);
            return $response->getContent();  
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la cr??ation du mouvement sur GDMAP"); 
            return []; 
        }
        
    }
    public function rechercheAgentGDMAP($chaine)
    {

        try {
            $response = $this->client->request('GET', $this->urlGDMAP . "recherche_agents/$chaine", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "X-AUTH-TOKEN" => $this->jetonGDMAP
                ],

            ]);

            return $response->getContent(); 
        } catch (\Throwable $th) {
            $this->logsErreur->ajouterLogWS($th->getMessage()); 
            throw new Exception("Erreur dans la recherche d'agent sur GDMAP"); 
            return []; 
        }

    }

    public function connexionJWT()
    {
        //Cette requete permet de s'authentifier aupr??s du serveur OVH
        $response = $this->client->request('POST', $this->urlAPIOVH. 'login', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'proxy' => $this->urlPROXY,
            'json' => ['username' => $this->mailAPI, 'password' => $this->motdepasseAPI],
        ]);
        
        $jsonReponse = json_decode($response->getContent());
        return $jsonReponse->token;
    }


    // json envoy?? est au format suivant 

    // [
    //     {"apiAdditionnalPassword" : mot depasse secret}, 
    //     {objet type candidat}
    // ]
    public function envoiTypeCandidat(TypeCandidat $typeCandidat)
    {
        //Normalise l'objet "TypeCandidat" pour en faire une cha??ne de caract??re json car ?? la base c'est un objet PHP 
        $typeCandidatNormalise = $this->normalizer->normalize($typeCandidat, null, ['groups' => 'type_candidat_serialize']);
        $json = json_encode($typeCandidatNormalise);
        //R??cup??re le mot de passe addtionnel que nous ajoutons dans le corps de chaque requ??te
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        //Forme une liste au format de chaine de caract??re JSON avec comme premier element de la liste l'objet TypeCandidat
        //Et comme deuxi??me ??l??ment le mot de passe additionnel
        $json = '[' . $json . ",{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}]"; 
        try{
            //R??cup??re le jeton JWT dans la session
            $tokenAuth = $this->session->get('tokenJWT'); 
            //Cr??er la chaine de caract??re ?? ajouter dans l'ent??te
            $authorization = 'Bearer '.$tokenAuth;
            //Envoi la requ??te et r??cup??re la r??ponse
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_type_candidat', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json,
            ]);
    
            //r??cup??re le code de la r??ponse
            $httpCode = $response->getStatusCode(); 
            //401 est le code renvoy?? quand le jeton JWT a expir?? 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            //La premi??re requ??te a ??chou?? car le jeton a expir?? 
            //On en reg??n??re un 
            $tokenAuth =  $this->connexionJWT();
            //On le stocke dans la session
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            //On renvoi la requ??te
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_type_candidat', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json,
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        //Si le code de retour est 500 on r??cup??re l'erreur et on l'ajoute dans le fichier de log
        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }

        // json envoy?? est au format suivant 

    // [
    //     {"apiAdditionnalPassword" : mot depasse secret}, 
    //     {objet type document}
    // ]
    public function envoiTypeDocument(TypeDocument $typeDocument)
    {
        $typeDocumentNormalise = $this->normalizer->normalize($typeDocument, null, ['groups' => 'type_document_serialize']);
        $json = json_encode($typeDocumentNormalise);
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = '[' . $json . ",{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}]"; 
        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_type_document', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json,
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_type_document', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json,
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }

        // json envoy?? est au format suivant 

    // [
    //     {"apiAdditionnalPassword" : mot depasse secret}, 
    //     {objet  candidat}
    // ]
    public function envoiCandidat(Candidat $candidat)
    {
        //Normalize le candidat grace aux annotiations group qui permettent de choisir les attributs que nous allons mettre dans l'??l??ment normaliz??. (Voir entit??e Candidat)
        //Nous avons mis tous les attributs sauf les dates qui seront ajout??es ?? la main. Car leur normalization ne se fait pas correctement
        //Pour ce qui est des entit??s avec laquelle l'entit?? candidat a des relations nous avons ajout??s ces entit??s avec des groupes de la mani??re suivante
        //candidat.typeCandidat --- > typeCandidat.documentsAFournir (tous les attributs de type document sauf documents et typeCandidat)
        //(tous les attributs de types candidats sont ajout??s dans la s??rialiation sauf typeCandidat.candidat pour ne pas faire des appels circulaires)
        $candidatNormalise = $this->normalizer->normalize($candidat, null, ['groups' => 'candidat_serialize']);

        //on encode l'entit?? en json elle deviens alors une chaine de caract??re
        $json = json_encode($candidatNormalise);
        //recup??re les dates en format string
        $datePrevisEmbauche = $candidat->getDatePrevisEmbauche()->format('d-m-Y');
        $delaiFormulaire = $candidat->getDelaiFormulaire()->format('d-m-Y');
        $chaine = ",\"datePrevisEmbauche\":\"$datePrevisEmbauche\"";
        $chaine .= ",\"delaiFormulaire\":\"$delaiFormulaire\"";

        if (null != $candidat->getDateDeNaissance()) {
            $dateDeNaissance = $candidat->getDateDeNaissance()->format('d-m-Y');
            $chaine .= ",\"dateDeNaissance\":\"$dateDeNaissance\"";
        }

        if (null != $candidat->getDateExpirationTs()) {
            $dateExpirationTs = $candidat->getDateExpirationTs()->format('d-m-Y');
            $chaine .= ",\"dateExpirationTs\":\"$dateExpirationTs\"";
        }

        $chaine .= '}';

        //On ajoute les dates ?? la chaine de caract??re json
        $json = substr_replace($json, $chaine, strlen($json) - 1);
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = '[' . $json . ",{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}]"; 

        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_candidat', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'recup_candidat', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        $jsonDecode = json_decode($response->getContent());
        if (500 == $httpCode) {
            throw new \Exception($response->getContent());
            $this->logsErreur->ajouterLogWS($response->getContent());
        }
    }

    public function recupTousInfos()
    {        
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = "{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}"; 
        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '. $tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. 'recup_tous_infos', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. 'recup_tous_infos', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
            $httpCode = $response->getStatusCode(); 
        }
        $jsonDecode = json_decode($response->getContent());  

        if (false != $jsonDecode && 200 == $httpCode ) {
            try {
                $candidatASupprimer = [];
                foreach ($jsonDecode as $candidatDecode) {
                    $candidatEncode = json_encode($candidatDecode->candidat);
                    $candidat = $this->serializer->deserialize($candidatEncode, Candidat::class, 'json');

                    //D??serialize les relations avec les autres classes pr??sentes dans la classe candidat
                    $candidat = $this->monserializeur->deserializeRelations($candidat, $candidatDecode); 
                    //Cette fonction permet de d??serializer les dates ce que ne permet pas de faire le deserializeur par d??faut. 
                    $candidat = $this->monserializeur->deserializeDates($candidat, $candidatDecode); 
                    //Tous les attributs ne sont pas pr??sents cot?? FrontOffice (Formulaire OVH)
                    //Lorsqu'on r??cup??re ils sont donc mis ?? null par d??faut. On leur redonne donc leurs valeurs initiales. 
                    $candidatAvantModif = $this->candidatRepository->findById($candidatDecode->candidat->id);
                    $candidat = $this->monserializeur->valeursPrecedentesAbsentFrontOffice($candidat, $candidatAvantModif); 

                    if (null != $this->candidatRepository->findById($candidatDecode->candidat->id)) {
                        $this->em->merge($candidat);
                    } else {
                        $this->em->persist($candidat);
                    }
                    
                    try {
                        $this->em->flush();
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    try {
                        //Si il y a des documents 
                        if (!empty($candidatDecode->candidat->documents)) {
                            //Pour chaque document
                            foreach ($candidatDecode->candidat->documents as $document) {
                                $documentEncodage = json_encode($document);
                                $documentObjet = $this->serializer->deserialize($documentEncodage, Document::class, 'json');
                                $documentObjet->removeAllType();
                                $type = $this->typeDocumentRepository->findOneById($document->type[0]->id);
                                $documentObjet->addType($type);

                                $documentObjet->setCandidat($this->candidatRepository->findById($candidat->getId()));
                                $documentObjet->setUrl($this->fichiersChemin.'/'.strtoupper($candidat->getNom()).' '.$candidat->getPrenom() . '/' . '0.EMBAUCHE'.'/'.$documentObjet->getNom()); 
                                //$documentObjet->setId($document->id);
                                if (!empty($this->documentRepository->findByNom($document->nom))) {
                                    try{
                                        $documentARemplacer = $this->documentRepository->findByNom($document->nom)[0]; 
                                        $this->em->remove($documentARemplacer); 
                                    }catch(\Exception $e){
                                        echo 'erreur'; 
                                    }
                                    $this->em->persist($documentObjet); 
                                } else {
                                    $this->em->persist($documentObjet);
                                }
                                $this->em->merge($type);
                                $this->em->flush();
                            }
                        }
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    try {
                        if (!empty($candidatDecode->fichiers)) {
                            //Cr??er un r??pertoire pour le candidat
                            $this->filesystem->mkdir($this->fichiersChemin.'/'.strval($candidat->getId()). '/' . '0.EMBAUCHE');
                            foreach ($candidatDecode->fichiers as $document) {
                                //d??code la chaine base_64 et la transforme en fichier
                                $fichier = base64_decode($document->code); 
                                //ajoute le fichier ?? sa place
                                file_put_contents($this->fichiersChemin.'/'.strval($candidat->getId()). '/' . '0.EMBAUCHE/' . $document->nomFichier, $fichier);
                            }
                        }
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                    $candidatASupprimer[] = $candidat->getId();
                }
                //Supprime toutes les informations r??cup??r??es du serveur
                $this->supprimeTousInfos($candidatASupprimer);
            } catch (\Exception $e) {
                $this->logsErreur->ajouterLogWS($e->getMessage());
                throw new \Exception($e->getMessage());
            }
        } elseif (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($reponse);
            throw new \Exception($reponse);
        }
    }


    public function supprimeTousInfos($idCandidats)
    {
        $json = json_encode($idCandidats);
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = '[' . $json . ",{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}]"; 

        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'supprime_tous_infos', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('POST', $this->urlAPIOVH. 'supprime_tous_infos', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }
    public function supprimeTypeCandidat($id)
    {
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = "{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}"; 
        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_type_candidat/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_type_candidat/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }

    public function supprimeCandidat($id)
    {
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = "{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}"; 
        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_candidat/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $this->session->set('tokenJWT', $tokenAuth);
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_candidat/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }
        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }

    public function supprimeTypeDocument($id)
    {
        $apiAdditionnalPassword = $this->apiAdditionnalPassword; 
        $json = "{\"apiAdditionnalPassword\": \"$apiAdditionnalPassword\"}"; 
        try{
            $tokenAuth = $this->session->get('tokenJWT'); 
            $authorization = 'Bearer '.$tokenAuth;
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_type_document/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
            if($httpCode == 401){
                throw new \Exception("Token JWT expir??"); 
            }
        }catch(\Exception $e){
            $tokenAuth =  $this->connexionJWT();
            $authorization = 'Bearer '.$tokenAuth;
            $this->session->set('tokenJWT', $tokenAuth);
            $response = $this->client->request('GET', $this->urlAPIOVH. "supprime_type_document/$id", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    "Authorization" => $authorization
                ],
                'proxy' => $this->urlPROXY,
                'body' => $json
            ]);
    
            $httpCode = $response->getStatusCode(); 
        }

        if (500 == $httpCode) {
            $this->logsErreur->ajouterLogWS($response->getContent());
            throw new \Exception($response->getContent());
        }
    }
}
