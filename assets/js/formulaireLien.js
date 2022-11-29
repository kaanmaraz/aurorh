import '../styles/formulaireLien.css'
import CollectionType from './Service/CollectionType';
import AdresseApi from './Service/AdresseApi';
import PaysApi from './Service/PaysApi';
import ApiGouv from './Service/ApiGouv';

$(".collection").each(function(){
    new CollectionType($(this), $(".element-collection-file").length); 
})
new AdresseApi($("#formulaire_candidat_adresse"), $("#formulaire_candidat_codePostal"), $("#formulaire_candidat_ville"))
new PaysApi($("#formulaire_candidat_paysNaissance"))
new PaysApi($("#formulaire_candidat_nationnalite"))
new ApiGouv($("#formulaire_candidat_villeNaissance"), 'communes')
new ApiGouv($("#formulaire_candidat_departementNaissance"), 'departements')