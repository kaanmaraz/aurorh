<?php

namespace App\Tests;

use App\Entity\Candidat;
use App\Entity\TypeCandidat;
use App\Entity\TypeDocument;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FonctionnelsTest extends KernelTestCase
{

    public function testArrayCandidat(){

        $typeDocument = (new TypeDocument())
                        ->setLibelle("Carte d'identité")
                        ->setFormat('pdf')
                        ->setMultiple(true); 

        $typeCandidat = (new TypeCandidat())
                        ->setLibelle("CDI");
                        // ->addDocumentsAFournir($typeDocument);

        $candidat = (new Candidat())
                        ->setNom("TestPrenom")
                        ->setPrenom("TestNom")
                        ->setPoste("TestPoste")
                        ->setTypeCandidat($typeCandidat); 

        var_dump((array) $typeCandidat); 
                        
    }

}