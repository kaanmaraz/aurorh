
import "../styles/modal-box.css"
import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
var candidatChoisi = null; 
var showbuttons = $(".action_button")
const axios = require("axios")


function listenFormMail() {

  // Place un event listener sur le formulaire 
  // Celui-ci déclenche une fonction lors de la soumission du formulaire 
  document.querySelector("form[name='mail_template']").addEventListener('submit', function (e) {
    // Annule le comportement par défaut qui est de recharger la page
    e.preventDefault(); 
    // Ne pas faire attentation à ça
    $(".modal-loader").show()
  
    // Envoyer une requete POST au serveur avec les infos du formulaire 
    // Ici j'utilise axios mais on peut fire avec JQuery ou bien fetch en Vanilla JS
    // this.action représente l'attribut action du formulaire c'est la route vers le controller par défaut de notre formulaire 
    // new FormData(e.target) contiens toutes les infos du formulaire 
    axios.post(this.action, new FormData(e.target))
    // Quand la reponse de ma requête est récue et qu'elle est favorable 
    .then(function (response) {
      // J'affiche une notif
      //la variable 'response' représente la réponse du serveur
      toastr.success("Template de mail enregistré avec succes")
    })
    .catch(function (error) {
      // Si une erreur est levée j'affiche une autre notif
      toastr.error(error.response.data);
    })
    // Ce then là s'éxecute dans tous les cas 
    .then(function () {
      $(".modal-loader").hide()
    });



    // Avec fetch tu ferais plutot comme ceci 
    fetch(this.action,new FormData(e.target))
        .then(function(response) {
          // Récupère la réponse du serveur et l'envoie au then suivant
          return response.json();
        })
        .then(function(json) {
          console.log(json);
        })
        .catch(function (error) {
          console.log(error);
        });

  
  })



}

function closeSpan() {
  $(".close").each(function () {
    $(this).on("click", function () {
      $("#action_modal_mail").hide()
    })
  })
  
}


showbuttons.each(function () {
  $(this).on("click", function () {
    $(".loader").show()
    candidatChoisi = $(this).attr("id").replace('envoi_mail_bouton_', ''); 
    axios.post('/candidat/get_mail_template/' + candidatChoisi)
    .then(function (response) {
      
      $("#action_modal_mail").show()
      $("#action_modal_mail .modal-content").append(response.data)
      listenFormMail(); 
      closeSpan(); 
    })
    .catch(function (error) {
      toastr.error(error.response.data);
    })
    .then(function () {
      $(".loader").hide()
    });
    
  })
})



 
