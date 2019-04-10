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

$('#clone-btn').on('click', function() {
    $('#cloneDialog').dialog('open');
});

$('#cloneCancel' ).button().on('click', function() {
    $('#cloneDialog').dialog('close');
});

$('#cloneSave').on('click', function() {
    let cloneId = document.getElementById('sectionData').dataset.id;
    let cloneName = $('#cloneName').val();
    let cloneSemester = document.querySelector('#selectableSemester > .ui-selected').innerHTML;
    let cloneYear = document.querySelector('#selectableYear > .ui-selected').innerHTML;
    let numberOfSlaves = $('#numberOfSlaves').val();

    if (cloneName && cloneSemester && cloneYear && numberOfSlaves && numberOfSlaves > 0) {   
        let requestPath = path({
            'section/clone': cloneId,
            name: cloneName,
            term: cloneSemester,
            year: cloneYear,
            numberOfSlaves: numberOfSlaves
        });
        $.ajax({
            url: requestPath,
            type: 'GET',
            success: function(data) {
                toastr.info(`${numberOfSlaves} slaves were made`);
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