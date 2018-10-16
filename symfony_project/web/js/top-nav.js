// Script to redirect to the selected user
function setImpersonate() {
    var sel = document.getElementById("impersonateSelect");
    if(sel.options[sel.selectedIndex].value != "-"){
        window.location = sel.options[sel.selectedIndex].value;
    } else{
        console.log("NO");
    }
}

if (document.querySelector('.jsData').dataset.route.includes('contest')) {
    $('#doc-links').hide();
    $('#doc-links').css('left', $('#doc-button').offset().left);
    $(window).click(function(event) {		
        if(event.target.id != "doc-button" && event.target.id != "doc-links"){
            $('#doc-links').hide();
        }
    });
    $('#doc-button').click( function() {
        if($('#doc-links').is(':hidden')){
            $('#doc-links').show();
        } else {
            $('#doc-links').hide();
        }
    });
}

document.querySelector('#hamburger-menu').onclick = () => {
    let body = document.querySelector('body');
    let leftNav = document.querySelector('.nav-left');
    if (body.classList.contains('hidden_hamburger')) {
        body.classList.remove('hidden_hamburger');
        leftNav.classList.remove('hidden_hamburger');
    } else {
        body.classList.add('hidden_hamburger');
        leftNav.classList.add('hidden_hamburger');
    }
}