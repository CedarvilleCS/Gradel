function collapseCard(cardID) {
    console.log(cardID);
    // active: true;
    $('#' + cardID).children().slideToggle();
    if($('#' + cardID).parent().find("h2").find("span").find("i").hasClass("fa-rotate-90")) {
        $('#' + cardID).parent().find("h2").find("span").find("i").removeClass("fa-rotate-90");
    } else {
        $('#' + cardID).parent().find("h2").find("span").find("i").addClass("fa-rotate-90");
    }
};