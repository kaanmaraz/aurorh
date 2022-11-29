import "select2/dist/css/select2.min.css"
import "select2/dist/js/select2.js"
import "../../styles/select2.css"
class AdresseApi
{
    constructor(adresseField, codePostalField, villeField){
        this.adresseField = adresseField
        this.codePostalField  = codePostalField
        this.villeField = villeField
        this.lieuList = new Object()
        this.addAdresseSelect2()
    }

    addAdresseSelect2(){
        this.adresseField.select2({
            ajax: {
                url: function (params) {
                    if (params.term && params.term.length > 3 && params.term.includes(' ')) {
                        return `https://api-adresse.data.gouv.fr/search/?q=${params.term}`;
                    }
                },
                dataType: 'json',
                processResults: (data) => {
                    let resultList = []
                    data.features.forEach(element => {
                        this.lieuList[element.properties.id] = {
                            adresse: element.properties.name,
                            codePostal: element.properties.postcode,
                            ville: element.properties.city
                        }

                        resultList.push({
                            "id": element.properties.label, 
                            "text": element.properties.label
                        })
                    });
                    
                    
                    return {
                        results: resultList
                    };
                }
            }, 
            tags: true, 
            placeholder: 'Cherchez votre adresse',
            width: "100%"
        });

        this.adresseField.on("change", (event) => {
            let id = $(event.currentTarget).val()
            let lieu = this.lieuList[id]
            if(lieu){
                this.codePostalField.val(lieu.codePostal)
                this.villeField.val(lieu.ville)
            }
        })
    }

}

export default AdresseApi