import "select2/dist/css/select2.min.css"
import "select2/dist/js/select2.js"
import "../../styles/select2.css"
class PaysApi
{
    constructor(paysField){
        this.paysField = paysField
        this.addPaysSelect2()
        this.affichageDateExpiration()
    }

    addPaysSelect2(){
        this.paysField.select2({
            ajax: {
                url: function (params) {
                    if (params.term && params.term.length > 1 ) {
                        return `https://restcountries.com/v3.1/name/${params.term}`;
                    }
                },
                dataType: 'json',
                processResults: (data) => {
                    let resultList = []
                    data.forEach(element => {
                        resultList.push({
                            "id": element.translations.fra.common, 
                            "text": element.translations.fra.common
                        })
                    });
                    
                    
                    return {
                        results: resultList
                    };
                }
            }, 
            tags: true, 
            placeholder: 'Cherchez un pays',
            width: "100%"
        });
    }

    affichageDateExpiration(){
        this.paysField.on("change", (event) => {
            if ($(event.currentTarget).val() == "France") {
                $("#formulaire_candidat_dateExpirationTs").parent().parent().hide()
            } else {
                $("#formulaire_candidat_dateExpirationTs").parent().parent().show()
            }
        })
    }

}

export default PaysApi