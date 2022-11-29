import EnregistreImageService from './EnregistreImageService.js'
import EnregistrePJService from './EnregistrePJService.js'
import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
const axios = require("axios")

class MailFormModalService{
  constructor(){
    this.showbuttons = null; 
    this.candidatChoisi = null; 
    this.closeSpan = null;
    this.enregistreImageService = null; 
  }

  
  addClickCloseSpan() {
    $(".close-mail-modal").each(function () {
      $(this).on("click", function () {
        $("#action_modal_mail").hide()
      })
    })
  }

  listenFormMail() {
    document.querySelector("form[name='mail_template']").addEventListener('submit',  (e) => {
      e.preventDefault(); 
      $(".modal-loader").show()
      axios.post('/candidat/mail_template/'+ this.candidatChoisi, new FormData(e.target))
      .then(function (response) {
        $("#action_modal_mail").hide()
        toastr.success(response.data)
      })
      .catch(function (error) {
        toastr.error( "Erreur dans l'envoi du mail" + error.response.data);
      })
      .then(function () {
        $(".modal-loader").hide()
      });
    
    })
  }

  addShowModalEvent(){

    $(".action_button").each( (index, element) => {
      $(element).on("click", (event) =>  {
        $(".loader").show()
        this.candidatChoisi = $(event.currentTarget).attr("id").replace('envoi_mail_bouton_', ''); 
        axios.post('/candidat/mail_template/' + this.candidatChoisi)
        .then( (response) => {
          $("#action_modal_mail").show()
          $("#action_modal_mail .modal-content").html("<span id='action_closespan_mail' class='close-mail-modal'>&times;</span>" + "<span class='modal-loader'></span>" + response.data)
            this.listenFormMail(); 
            this.addClickCloseSpan(); 
            this.initEnregistreImageService(); 
            this.initEnregistrePJService(); 
        })
        .catch(function (error) {
          console.log(error);
        })
        .then(function () {
          $(".loader").hide()
        });
        
      })
    })
  }

  initEnregistreImageService(){
    this.enregistreImageService = new EnregistreImageService(document.querySelector("form[name='add_image_mail_template']"), 'image'); 
    this.enregistreImageService.addOnsubmitFile(); 
    this.enregistreImageService.addSupprimeOnclick();   
    this.enregistreImageService.addCloseOnclick(); 
    this.enregistreImageService.addShowOnclick(); 
  }

  initEnregistrePJService(){
    this.enregistrePJService = new EnregistrePJService(document.querySelector("form[name='add_pj_mail_template']"),'pj' );  
    this.enregistrePJService.addOnsubmitFile(); 
    this.enregistrePJService.addSupprimeOnclick();  
    this.enregistrePJService.updateInclure();  
  }
}

export default MailFormModalService




 
