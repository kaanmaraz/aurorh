// import '../styles/verificationFormulaire.css'
// const axios = require("axios")
// const reader = new FileReader();
// var result
// $(".apercu-fichier").each(function () {
//     $(this).on("click", function () {
//         let documentId = $(this).attr("id")
//         axios.get("/formulaire/candidat/apercu_document/"+documentId)
//             .then(function (response) {
                
//                 let blob = new Blob([response.data], {type : 'mime/type'});
//                 reader.readAsText(blob)
//                 reader.onload = () => {
//                     let apercu = reader.result

//                     ``
//                     $("#apercu_document .modal-content").append(apercu); 
//                     $("#apercu_document").show()

//                     uploadedFile = reader.result;
//                     document.querySelector('#display_file').style.backgroundImage = url ({uploadedFile});
//                     reader.readDataURL(this.files[0]);
//                 }
 
//             })
//             .catch(function (error) {
//                 console.log(error);
//             })
//             .then(function () {
//                 console.log("Fin du loader")
//             })
//     })
// })