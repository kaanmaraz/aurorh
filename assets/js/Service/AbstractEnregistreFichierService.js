class AbstractEnregistreFichierService {
  constructor(form,type) {
    if (this.constructor === AbstractEnregistreFichierService) {
      throw new TypeError('Abstract class "AbstractEnregistreFichierService" cannot be instantiated directly');
    }
    this.form = form 
    this.type = type
  }
  
  addSupprimeOnclick() {
    throw new Error('You must implement this function');
  }
  
  addOnsubmitFile() {
    throw new Error('You must implement this function');
  }
  }

  export default AbstractEnregistreFichierService