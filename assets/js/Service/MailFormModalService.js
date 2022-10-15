import EnregistreImageService from './EnregistreImageService.js'
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
    document.querySelector("form[name='mail_template']").addEventListener('submit', function (e) {
      e.preventDefault(); 
      $(".modal-loader").show()
      axios.post(this.action, new FormData(e.target))
      .then(function (response) {
        toastr.success("Template de mail enregistré avec succes")
      })
      .catch(function (error) {
        toastr.error(error.response.data);
      })
      .then(function () {
        $(".modal-loader").hide()
      });
    
    })
  }

  addShowModalEvent(){
    var objectMailFormModalService = this;
    $(".action_button").each(function () {
      $(this).on("click", function () {
        $(".loader").show()
        this.candidatChoisi = $(this).attr("id").replace('envoi_mail_bouton_', ''); 
        axios.post('/candidat/get_mail_template/' + this.candidatChoisi)
        .then(function (response) {
          
          $("#action_modal_mail").show()
          $("#action_modal_mail .modal-content").append(response.data)
          objectMailFormModalService.listenFormMail(); 
          objectMailFormModalService.addClickCloseSpan(); 
          objectMailFormModalService.initEnregistreImageService(); 
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
    this.enregistreImageService = new EnregistreImageService(); 
    this.enregistreImageService.formImage = document.querySelector("form[name='add_image_mail_template']");  
    this.enregistreImageService.addOnsubmitImage(); 
    this.enregistreImageService.addSupprimeOnclick();   
    this.enregistreImageService.addCloseOnclick(); 
    this.enregistreImageService.addShowOnclick(); 
  }
}

var mailFormModalService = new MailFormModalService(); 
mailFormModalService.addShowModalEvent(); 






 
