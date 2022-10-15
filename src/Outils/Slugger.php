<?php

namespace App\Outils;

class Slugger
{
    public function toSlug($chaine)
    {
        $libelle = str_replace(' ', '_', $chaine);
        $libelle = str_replace('é', 'e', $libelle);
        $libelle = str_replace('è', 'e', $libelle);
        $libelle = str_replace("'", '', $libelle);
        $libelle = str_replace('ô', 'o', $libelle);
        $libelle = str_replace('à', 'a', $libelle);
        $libelle = str_replace('ç', 'c', $libelle);
        $libelle = str_replace('ù', 'u', $libelle);
        $libelle = str_replace('â', 'a', $libelle);
        $libelle = str_replace('û', 'u', $libelle);
        $libelle = str_replace('ê', 'e', $libelle);
        $libelle = strtolower($libelle);
        return $libelle; 
    }
}
