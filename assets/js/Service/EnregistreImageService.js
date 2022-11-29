import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
import AbstractEnregistreFichierService from './AbstractEnregistreFichierService';
const axios = require("axios")

class EnregistreImageService extends AbstractEnregistreFichierService {
  constructor(form , type ) {
    super(form, type)
  }


  addShowOnclick() {
    $(".apercu-"+this.type).each(function () {
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
    $(".listeUrls-"+this.type+" .supprime-"+ this.type).each(function () {
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
  
  addOnsubmitFile() {
    this.form.addEventListener('submit',  (e) =>  {
      e.preventDefault(); 
      $(".loader").show()
      axios.post(this.form.action, new FormData(e.target))
        .then( (response) => {
          document.querySelector(".listeUrls-"+this.type).innerHTML += "<li class='list-group-item' ><a href='"+ response.data +"' >"+ response.data +"</a><button id="+response.data+" class='apercu-image btn btn-xs btn-info' >Aperçu</button><button id='"+ response.data +"' class='supprime-"+this.type+" btn btn-xs btn-danger'> <i class='fas fa-times'></i> </button></li>"
          this.addSupprimeOnclick();   
          this.addCloseOnclick(); 
          this.addShowOnclick(); 
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

export default EnregistreImageService; 