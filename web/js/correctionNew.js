$(document).ready(function(){

  $('#form_position').specialedit(['R°', 'V°'], {shift: false});
  $('#form_description').specialedit(['→'], {shift: false});
  $('#editionTitle').change(function(event){
    $('#editionSort').val(this.value);
    if(this.value == 1){
      $('#form_tm').removeAttr('required');
      $('#form_hgv').removeAttr('required');
      $('#form_ddb').removeAttr('required');
    } else {
      $('#form_tm').attr('required', 'required');
      $('#form_hgv').attr('required', 'required');
      $('#form_ddb').attr('required', 'required');
    }
  });

  $('#editionSort').change(function(event){
    $('#editionTitle').val(this.value);
    if(this.value == 1){
      $('#form_tm').removeAttr('required');
      $('#form_hgv').removeAttr('required');
      $('#form_ddb').removeAttr('required');
    } else {
      $('#form_tm').attr('required', 'required');
      $('#form_hgv').attr('required', 'required');
      $('#form_ddb').attr('required', 'required');
    }
  });
  
  $('#form_tm').change(function(event){
    numberWizard($(this).val(), $(this).attr('wizard-url'));
  });

  $('#form_hgv').change(function(event){
    numberWizard($(this).val(), $(this).attr('wizard-url'));
  });

  $('#form_ddb').change(function(event){
    numberWizard($(this).val(), $(this).attr('wizard-url'));
  });
  
  $('#editionSort').change(function(event){
    numberWizard({text: $('#form_text').val(), editionId: $(this).val()}, $(this).attr('wizard-url'));
  });
  
  $('#editionTitle').change(function(event){
    numberWizard({text: $('#form_text').val(), editionId: $(this).val()}, $(this).attr('wizard-url'));
  });

  $('#form_text').change(function(event){
    numberWizard({text: $(this).val(), editionId: $('#editionSort').val()}, $(this).attr('wizard-url'));
  });
  
  $('#newAndAgain').click(function(event){
    $('#redirectTarget').val('new');
  });

  $('#new').click(function(event){
    $('#redirectTarget').val('show');
  });

});