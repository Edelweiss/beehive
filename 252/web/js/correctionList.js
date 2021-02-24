function printCorrectionList(){
  var form = $('#printForm');
  var grid = $('#list');
  
  var postData = grid.jqGrid('getGridParam', 'postData');
  $.each(postData, function(key, value) {
    form.append('<input type="hidden" name="' + key + '" value="' + value + '" />');
    console.log(key+" = "+value);
  });

  var colModel = $('#list').jqGrid('getGridParam', 'colModel');
  var visible = '';
  $(colModel).each(function(index, column){
    if(!column['hidden']){
      visible += column['name'] + ';';
    }
  });
  form.append('<input type="hidden" name="visible" value="' + visible + '" />');

  $('#printForm').submit();

}