function numberWizard(id, path){
  if(id.length){
    $.post(path, {'id': id}, function(data){
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

      } else {
         //console.log('error');
         //console.log(data);
      }
    }, 'json');
  }
}

function deleteTask_click(task, event, path){
  var id = task.id.match(/^[^\d]+(\d+)$/)[1];
  path = path.replace('0', id);
  var category = $(task.parentNode).text();
  category = category.substring(0, category.indexOf(':'));
  var description = $(task.parentNode).find('span.greek').text();
  
  if(confirm('Aufgabe »' + category + ': ' + description + '« wirklich unwiederbringlich aus der Datenbank entfernen?')){
    $.post(path, function(data){
      if(data.success){
          $(task.parentNode).remove();
          preview();
       } else {
         //console.log('error');
         //console.log(data);
       }
    }, 'json');
  }
}

function editTask_submitdata(task, value, settings){
  settings.target = settings.target.match(/^(.+\/)\d+$/)[1] + task.id.match(/^task_(\d+)_(\w+)$/)[1];
  return {elementid: task.id.match(/^task_(\d+)_(\w+)$/)[2]};
}
