import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
const axios = require("axios")

class EnregistreImageService {
  constructor() {
    this.formImage = null; 
  }


  addShowOnclick() {
    $(".apercu").each(function () {
      $(this).on("click", function () {
        let imageAMontrerUrl = $(this).attr("id")
        $("#show_image_modal .modal-content .image").html("<img src='"+ imageAMontrerUrl +"'/>")
        $("#show_image_modal").show()
    
      })
    })
  }
  
  
  addCloseOnclick() {
    $(".close-image").each(function () {
      $(this).on("click", function () {
        $("#show_image_modal").hide()
      })
    })
  }
  
   
  
  
  addSupprimeOnclick() {
    $("#listeUrls .supprime").each(function () {
          let bouton = $(this)
          let urlImage = $(this).attr("id")
          $(this).on("click", function () {
            $(".loader").show()
              axios.post('/api_delete_image', {
                  url: urlImage
              })
              .then(function (response) {
                  toastr.success(response.data);
                  bouton.parent().remove()
              })
              .catch(function (error) {
                  toastr.error(error.response.data);
              })
              .then(function () {
                $(".loader").hide()
              });
          })
      })
  
  }
  
  addOnsubmitImage() {
    var objectEnregistreImageService = this; 
    this.formImage.addEventListener('submit', function (e) {
      e.preventDefault(); 
      $(".loader").show()
      axios.post(this.action, new FormData(e.target))
        .then(function (response) {
          document.querySelector("#listeUrls").innerHTML += "<li class='list-group-item' ><a href='"+ response.data +"' >"+ response.data +"</a><button id='"+ response.data +"' class='apercu btn btn-xs btn-info' >Aperçu</button><button id='"+ response.data +"' class='supprime btn btn-xs btn-danger'> <i class='fas fa-times'></i> </button></li>"
          objectEnregistreImageService.addSupprimeOnclick();   
          objectEnregistreImageService.addCloseOnclick(); 
          objectEnregistreImageService.addShowOnclick(); 
  
      })
        .catch(function (error) {
          toastr.error(error);
        })
        .then(function () {
          $(".loader").hide()
        });
    
    })
  }
}



if (document.querySelector("form[name='add_image_mail_template']") != null) {
  var enregistreImageService = new EnregistreImageService(); 
  enregistreImageService.formImage = document.querySelector("form[name='add_image_mail_template']");  
  enregistreImageService.addOnsubmitImage(); 
  enregistreImageService.addSupprimeOnclick();   
  enregistreImageService.addCloseOnclick(); 
  enregistreImageService.addShowOnclick(); 
}


export default EnregistreImageService; 