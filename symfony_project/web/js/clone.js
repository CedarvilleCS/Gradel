$('#cloneDialog').dialog({
    autoOpen: false,
    dialogClass: 'no-close',
    draggable: false,
    resizable: false,
    modal: true,
    my: 'center',
    at: 'center',
    of: window   
});

$('#clone-btn').button().on('click', function() {
    $('#cloneDialog').dialog('open');
});

$('#cloneCancel' ).button().on('click', function() {
    $('#cloneDialog').dialog('close');
});

$('#cloneSave').on('click', function() {
    var cloneId = document.getElementById('sectionData').dataset.id;
    var cloneName = $('#cloneName').val();
    var cloneSemester = document.querySelector('#selectableSemester > .ui-selected').innerHTML;
    var cloneYear = document.querySelector('#selectableYear > .ui-selected').innerHTML;
    var numberOfClones = $('#numberOfClones').val();

    if (cloneName && cloneSemester && cloneYear && numberOfClones && numberOfClones > 0) {   
        var requestPath = path({
            'section/clone': cloneId,
            name: cloneName,
            semester: cloneSemester,
            year: cloneYear,
            numberOfClones: numberOfClones
        });
        $.ajax({
            url: requestPath,
            type: 'GET',
            success: function(data) {
                toastr.info(`${numberOfClones} clones were made`);
            },
            error: function(error) {
                toastr.error('Cloning was unsuccessful')
           }
        });

        $('#cloneDialog').dialog( 'close' );
    }
});

$('#selectableSemester').selectable();
$('#selectableYear').selectable();