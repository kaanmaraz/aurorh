import $ from 'jquery';
// create global $ and jQuery variables
global.$ = global.jQuery = $;
import 'bootstrap' ;
import '../styles/tableau.css'
require('jszip')
require('datatables.net');
require('datatables.net-bs4');
require('datatables.net-select-bs4');
require('datatables.net-searchpanes-bs4');
require('datatables.net-buttons-bs4');
import 'datatables.net-bs4/css/dataTables.bootstrap4.css'
import 'datatables.net-select-bs4/css/select.bootstrap4.css'
import 'datatables.net-searchpanes-bs4/css/searchPanes.bootstrap4.css'
import 'datatables.net-buttons-bs4/css/buttons.bootstrap4.css'

const axios = require("axios")
import * as toastr from 'toastr';
import 'toastr/build/toastr.css';
import fr from "./datatable-fr.js"

$(document).ready( function () {
    $('#table').DataTable({
        paging: true, 
        ordering: true,
        language: fr, 
        buttons: [
            {
                extend: 'searchPanes',
                config: {
                    cascadePanes: true, 
                }, 
                className: 'bouton-filtrage btn btn-light', 
            }, 
        ],
        dom: 'Bfrtip', 
        columnDefs: [
            {
                searchPanes: { 
                    show: true, 
                    initCollapsed: true
                },
                targets: [0,1,2,3,4,5],
            },
            {
                orderable: false,
                targets: [6,7],
            },
        ]
    });


} );

$(".menu > li").each(function () {
    $(this).on("click", function () {
        if ($(this).find(".sub-menu").css("transform") == "matrix(0, 0, 0, 0, 0, 0)") {
            $(this).find(".sub-menu").css("transform", "scale(1)"); 
            $(this).find(".sub-menu li").css("transform", "scale(1)"); 
        } else {
            $(this).find(".sub-menu").css("transform", "scale(0)");
            $(this).find(".sub-menu li").css("transform", "scale(0)"); 
        }
    })
})

$("input[type='checkbox']").each(function () {
    $(this).on("change", function () {

        if ($(this).attr("id") == "tout" && $(this).is(":checked")) {
            $("input[type='checkbox']").each(function () {
                $(this).prop('checked', true);
            })
        }else if ($(this).attr("id") == "tout" && !$(this).is(":checked")){
            $("input[type='checkbox']").each(function () {
                $(this).prop('checked', false);
            })
        }

        if ($("#supprimer-selection button").length == 0  && $("td input[type='checkbox']:checked").length !== 0) {
            $("#supprimer-selection").append("<button class='btn btn-danger'>Supprimer la séléction</button>")
            $("#supprimer-selection button").on("click", function () {
                let aSupprimer = $("input[type='checkbox']:checked"); 
                if (aSupprimer.length !== 0) {
                    let idsASupprimer;
                    idsASupprimer = [];
                    $("input[type='checkbox']:checked").each(function () {
                        if ($(this).attr("id") !== "tout") {
                            idsASupprimer.push($(this).parent().parent().attr("id"));
                        }else{
                            idsASupprimer.push($(this).attr("id"))
                        }
                    })
                    if (typeDonnee == "type/candidat") {
                        if (window.confirm("Voulez vous vraiment supprimer ce(s) types de contrat ? Si des candidats sont liés à ces contrats leurs types de contrats deviendront nuls")) {
                            $(".loader").show()
                            axios.post('/'+typeDonnee+'/delete_list', {
                                liste: idsASupprimer,
                              })
                              .then(function (response) {
                                if (response.status == 200) {
                                    document.location.reload(true); 
                                    toastr.success("Elements supprimés avec succès")
                                } 
        
                              })
                              .catch(function (error) {
                                toastr.error(error.response.data);
                              })
                              .then(function () {
                                $(".loader").hide()
                              });
                        }
                    }else{
                        $(".loader").show()
                        axios.post('/'+typeDonnee+'/delete_list', {
                            liste: idsASupprimer,
                          })
                          .then(function (response) {
                            if (response.status == 200) {
                                document.location.reload(true); 
                                toastr.success("Elements supprimés avec succès")
                            } else {
                                document.location.reload(true); 
                                toastr.error("Erreur dans la suppression des éléments")
                            }
    
                          })
                          .catch(function (error) {
                            toastr.error("Erreur dans la suppression des éléments");
                          })
                          .then(function () {
                            $(".loader").hide()
                          });
                    }

                    
                }
            })
        }else if( $("input[type='checkbox']:checked").length == 0 ){
            $("#supprimer-selection button").remove()
        }
        
    })
})