$('.flyTo').change(function(event){ flyTo(this.id, this.value); }).keyup( function(event){if(event.which == 13){ flyTo(this.id, this.value); } });
$('#ddb').autocomplete({source: document.location.href.replace('browse', 'numberWizard/autocomplete/ddb'), select: function(event, ui){ flyTo(this.id, ui.item.value); } });

function flyTo(type, id){
  if(id != ''){
    document.location.href = document.location.href.replace('browse', type) + '/' + id;
  }
}

