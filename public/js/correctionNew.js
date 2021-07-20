function registerWizard(id, path) {
    $.post(path.replace('/0', '/' + id), {}, function(data) {
        if (data.success) {
            if (data.data.bl.edition) {
                $('#editionSort').val(data.data.bl.edition);
                $('#editionTitle').val(data.data.bl.edition);
            }
            if (data.data.bl.text) {
                $('#correction_new_text').val(data.data.bl.text);
            }
        }
    }, 'json');
}

function registerApiary(id, path) {
    $('div.correctionList').load(path.replace('/0', '/' + id));
}

$(document).ready(function() {
    $('#form_position').specialedit(['R°', 'V°'], { shift: false });
    $('#form_description').specialedit(['→'], { shift: false });
    $('#editionTitle').change(function(event) {
        $('#editionSort').val(this.value);
        if (this.value == 1) {
            $('#form_tm').removeAttr('required');
            $('#form_hgv').removeAttr('required');
            $('#form_ddb').removeAttr('required');
        } else {
            $('#form_tm').attr('required', 'required');
            $('#form_hgv').attr('required', 'required');
            $('#form_ddb').attr('required', 'required');
        }
    });

    $('#editionSort').change(function(event) {
        $('#editionTitle').val(this.value);
        if (this.value == 1) {
            $('#form_tm').removeAttr('required');
            $('#form_hgv').removeAttr('required');
            $('#form_ddb').removeAttr('required');
        } else {
            $('#form_tm').attr('required', 'required');
            $('#form_hgv').attr('required', 'required');
            $('#form_ddb').attr('required', 'required');
        }
    });

    $('#editionSort').change(function(event) {
        numberWizard({ text: $('#correction_new_text').val(), editionId: $(this).val() }, $(this).attr('wizard-url'));
    });

    $('#editionTitle').change(function(event) {
        numberWizard({ text: $('#correction_new_text').val(), editionId: $(this).val() }, $(this).attr('wizard-url'));
    });

    $('#correction_new_text').change(function(event) {
        numberWizard({ text: $(this).val(), editionId: $('#editionSort').val() }, $(this).attr('wizard-url'));
    });

    $('#newAndAgain').click(function(event) {
        $('#redirectTarget').val('new');
    });

    $('#new').click(function(event) {
        $('#redirectTarget').val('show');
    });

});