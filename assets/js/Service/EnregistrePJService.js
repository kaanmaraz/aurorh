import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
import AbstractEnregistreFichierService from './AbstractEnregistreFichierService';
const axios = require("axios")

class EnregistrePJService extends AbstractEnregistreFichierService {
  constructor(form, type) {
    super(form, type)
  }

  updateInclure(){
    $(".listeUrls-"+this.type+" .inclu-pj").each(function () {
      $(this).on("change", function () {
        let id =   $(this).attr('id').replace('inclu_','')
        axios.post('/api_update_pj_actif/'+id, {
          actif: this.checked
        })
        .catch(function (error) {
          console.log(error);
        })
      })  
    })
  }
  
  addSupprimeOnclick() {
    $(".listeUrls-"+this.type+" .supprime-"+ this.type).each(function () {
          let bouton = $(this)
          let id = $(this).attr("id")
          $(this).on("click", function () {
            $(".loader").show()
              axios.post('/api_delete_pj', {
                  id: id
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
    this.form.addEventListener('submit',  (e) => {
      e.preventDefault(); 
      $(".loader").show()
      axios.post(this.form.action, new FormData(e.target))
        .then( (response) => {
          let pieceJointe = JSON.parse(response.data)
          document.querySelector(".listeUrls-"+this.type).innerHTML += "<li class='list-group-item' ><a href='"+ pieceJointe.url +"' >"+ pieceJointe.nom +"</a><div>Inclure: <input class='inclu-pj' type='checkbox' name='inclu-pj' id='inclu_'"+pieceJointe.id+"' checked="+pieceJointe.actif+" ></div><button id='"+ pieceJointe.id +"' class='supprime-"+this.type+" btn btn-xs btn-danger'> <i class='fas fa-times'></i> </button></li>"
          this.addSupprimeOnclick();   
          this.updateInclure();
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

export default EnregistrePJService; 