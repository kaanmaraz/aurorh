import EnregistreImageService from "./Service/EnregistreImageService"; 
import EnregistrePJService from "./Service/EnregistrePJService";
import '../styles/enregistreFichiers.css'

$(document).ready(function () {
  if (document.querySelector("form[name='add_image_mail_template']") != null) {
    var enregistreImageService = new EnregistreImageService(document.querySelector("form[name='add_image_mail_template']"), 'image'); 
    enregistreImageService.addOnsubmitFile(); 
    enregistreImageService.addSupprimeOnclick();   
    enregistreImageService.addCloseOnclick(); 
    enregistreImageService.addShowOnclick(); 
  }

  if (document.querySelector("form[name='add_pj_mail_template']") != null) {
    var enregistrePJService = new EnregistrePJService(document.querySelector("form[name='add_pj_mail_template']"),'pj' );  
    enregistrePJService.addOnsubmitFile(); 
    enregistrePJService.addSupprimeOnclick();  
    enregistrePJService.updateInclure(); 
  }
})
