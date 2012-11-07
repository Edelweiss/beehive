$.fn.togglepanels = function(){
  return this.each(function(){
    $(this).addClass("ui-accordion ui-accordion-icons ui-widget ui-helper-reset")
  .find("h4") // changed h3 to h4
    .addClass("ui-accordion-header ui-helper-reset ui-state-default ui-corner-top ui-corner-bottom")
    .hover(function() { $(this).toggleClass("ui-state-hover"); })
    .prepend('<span class="ui-icon ui-icon-triangle-1-e"></span>')
    .click(function() {
      $(this)
        .toggleClass("ui-accordion-header-active ui-state-active ui-state-default ui-corner-bottom")
        .find("> .ui-icon").toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s").end()
        .next().slideToggle();
      return false;
    })
    .next()
      .addClass("ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom")
      .hide();
  });
}; // http://jsfiddle.net/DkHyd/

function numberWizard(id, path){
  var parameters = typeof id == 'object' ? id : {'id': id};

  $.post(path, parameters, function(data){
    if(data.success){

      if(data.data.tm.length){
        $('#form_tm').val(data.data.tm[0]);
      }
      if(data.data.hgv.length){
        $('#form_hgv').val(data.data.hgv[0]);
      }
      if(data.data.ddb.length){
        $('#form_ddb').val(data.data.ddb[0]);
      }
      if(data.data.bl.edition){
        $('#editionSort').val(data.data.bl.edition);
        $('#editionTitle').val(data.data.bl.edition);
      }
      if(data.data.bl.text){
        $('#form_text').val(data.data.bl.text);
      }
      
      duplicateCheck(path.replace(/^(.+)numberWizard.*$/, '$1') + 'hgv/' + $('#form_hgv').val() + '/xml');

    } else {
       //console.log('error');
       //console.log(data);
    }
  }, 'json');

}

function duplicateCheck(path){
  $.post(path, null, function(data){
    if(data.success){
      if(data.data.count){
        var hgv = $('#form_hgv').val();
        var a = $('#duplicateLink');
        a.attr('href', a.attr('href').replace(/\d+$/, hgv));
        $('#duplicateWarning').show();
      } else {
        $('#duplicateWarning').hide();
      }  
    } else {
    }
  }, 'json');
}

function dialogue_cancel(dialogue, type){
  $('form#' + type + 'New')[0].reset();
  $(dialogue).dialog('close');
}

function dialogue_ok(dialogue, type, pathNew, pathSnippet){

   $.post(pathNew, $('form#' + type + 'New').serialize(), function(data){
     if(data.success){

       // update dom
       var ul = $('div#' + type + ' > ul.' + type).first();
       if(!ul || !ul.length){
         $('div#' + type + ' > p.' + type).replaceWith('<ul class="' + type + '"></ul>');
         ul = $('div#' + type + ' > ul.' + type).first();
       }
       ul.append('<li></li>');
       var li = ul.find('li').last();
       li.load(pathSnippet.replace('0', data.data.id));

       // update preview
       preview();

       // close dialogue
       $('form#' + type + 'New')[0].reset();
       $(dialogue).dialog('close');

     } else {
       //console.log('error');
       //console.log(data);
     }

   }, 'json');
}

function deleteObject_click(object, event, path){
  var id = object.id.match(/^[^\d]+(\d+)$/)[1];
  path = path.replace('0', id);
  //var category = $(task.parentNode).text();
  //category = category.substring(0, category.indexOf(':'));
  //var description = $(task.parentNode).find('span.greek').text();

  if(confirm('Element wirklich unwiederbringlich aus der Datenbank entfernen?')){
    $.post(path, function(data){
      if(data.success){
          $(object.parentNode).remove();
          preview();
       } else {
         //console.log('error');
         //console.log(data);
       }
    }, 'json');
  }
}

function editObject_submitdata(object, value, settings){
  settings.target = settings.target.match(/^(.+\/)\d+$/)[1] + object.id.match(/^(task|index)_(\d+)_(\w+)$/)[2];
  return {elementid: object.id.match(/^(task|index)_(\d+)_(\w+)$/)[3]};
}
