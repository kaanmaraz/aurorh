import 'bootstrap-icons/icons/trash.svg'

class CollectionType
{

    constructor(field, nbElement = 0){
        this.nbElement = nbElement
        this.field = field
        this.init()
        this.addBouton = field.parent().find(".ajout")
        this.ajoutListener(); 
        this.addBouton.trigger("click")
        
    }

    init(){
        this.field.find(".element-collection-file").each(function(){
            if($(this).find(".suppression").length == 0){
                $(this).find("input").after(`<div class='col-md-1 suppression'><button type='button' class='btn btn-xs btn-danger'><i class='bi bi-trash'></i>Retirer</button></div>`)
            }
        })
    }

    ajoutListener(){
        this.addBouton.on("click", () => {
            let widget = this.field.attr("data-prototype")
            widget = widget.replace(/__name__label__/g, '')
            widget = widget.replace(/__name__/g, this.nbElement)
            this.field.append(widget); 
            this.nbElement++; 
            this.suppressionListener(); 
        })
    }

    suppressionListener(){
        this.field.find(".suppression").each((index, element) => {
            $(element).find("button.btn-danger").on("click", (event) => {
                $(event.currentTarget).parent().parent().remove(); 
                this.nbElement--; 
            })
        })
    }
}

export default CollectionType