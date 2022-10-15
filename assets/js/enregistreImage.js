import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
import '../styles/enregistreImage.css'
import '../styles/modal-box.css'
const axios = require("axios")
var formImage =  document.querySelector("form[name='add_image_mail_template']");  

function addShowOnclick() {
  $(".apercu").each(function () {
    $(this).on("click", function () {
      let imageAMontrerUrl = $(this).attr("id")
      $("#show_image_modal .modal-content .image").html("<img src='"+ imageAMontrerUrl +"'/>")
      $("#show_image_modal").show()
  
    })
  })
}


function addCloseOnclick() {
  $(".close").each(function () {
    $(this).on("click", function () {
      $("#show_image_modal").hide()
    })
  })
}

 


function addSupprimeOnclick() {
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

formImage.addEventListener('submit', function (e) {
    e.preventDefault(); 
    $(".loader").show()
    axios.post(this.action, new FormData(e.target))
      .then(function (response) {
        document.querySelector("#listeUrls").innerHTML += "<li class='list-group-item' ><a href='"+ response.data +"' >"+ response.data +"</a><button id='"+ response.data +"' class='apercu btn btn-xs btn-info' >Aperçu</button><button id='"+ response.data +"' class='supprime btn btn-xs btn-danger'> <i class='fas fa-times'></i> </button></li>"
        addSupprimeOnclick();   
        addCloseOnclick(); 
        addShowOnclick(); 

    })
      .catch(function (error) {
        toastr.error(error.response.data);
      })
      .then(function () {
        $(".loader").hide()
      });
  
})
addSupprimeOnclick();   
addCloseOnclick(); 
addShowOnclick(); 