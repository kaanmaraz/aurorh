import '../styles/candidatForm.css'
import '../styles/formulaireLien.css'
import CollectionType from './Service/CollectionType'
$(document).ready(function () {
    if ($("#candidat_typeCandidat").find("option:selected").text() == "CDI") {
        $("label[for='candidat_debutCDD']").parent().parent().hide();
        $("label[for='candidat_finCDD']").parent().parent().hide();
    } else {
        $("label[for='candidat_debutCDD']").parent().parent().show();
        $("label[for='candidat_finCDD']").parent().parent().show();
    }
    $("#candidat_typeCandidat").on("change", function () {
        if ($(this).find("option:selected").text() == "CDI") {
            $("label[for='candidat_debutCDD']").parent().parent().hide();
            $("label[for='candidat_finCDD']").parent().parent().hide();
        } else {
            $("label[for='candidat_debutCDD']").parent().parent().show();
            $("label[for='candidat_finCDD']").parent().parent().show();
        }
    })
    $(".collection").each(function(){
        new CollectionType($(this), $(".element-collection-file").length); 
    })

})



