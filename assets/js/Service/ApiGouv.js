// curl 'https://geo.api.gouv.fr/communes?nom=Nantes&fields=departement&boost=population&limit=5'
import "select2/dist/css/select2.min.css"
import "select2/dist/js/select2.js"
import "../../styles/select2.css"
class ApiGouv
{
    constructor(field, decoupage){
        this.field = field
        this.decoupage = decoupage
        this.addFieldSelect2()
    }

    addFieldSelect2(){
        this.field.select2({
            ajax: {
                url: (params) => {
                    if (params.term && params.term.length > 1) {
                        return `https://geo.api.gouv.fr/${this.decoupage}?nom=${params.term}`;
                    }
                },
                dataType: 'json',
                processResults: (data) => {
                    let resultList = []
                    data.forEach(element => {
                        resultList.push({
                            "id": element.nom, 
                            "text": element.nom
                        })
                    });
                    
                    return {
                        results: resultList
                    };
                }
            }, 
            tags: true, 
            placeholder: this.decoupage,
            width: "100%"
        });
    }

}

export default ApiGouv