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
