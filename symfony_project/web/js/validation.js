/* Client-Side Field Validation */
function setInvalid(element) {
	
	element.css('border', '3px solid red');
	element.css('background-color', '#FD8177');
}
  
function setValid(element) {
	element.css('border', '');
	element.css('background-color', '');
}
  
  
  
  function validate(element) {
    $('#save-btn').on('click', function(){          
      if ($.trim(element.val())  === '') {	
        setInvalid(element);
      }
    });
    
    /* Mark fields as valid once the user changes them */
    element.on('input',function(e){
      setValid(element);
    });
  }

  function validateMessage(element, label, message) {
    validate(element);
    $('#save-btn').on('click', function(){          
        label.append(message);
    });
  }