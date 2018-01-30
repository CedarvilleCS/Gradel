function collapseCard(cardID) {
    console.log(cardID);
    // active: true;
    $('#' + cardID).children().slideToggle({"duration": 200});
    if($('#' + cardID).parent().find("h2").find("span").find("i").hasClass("fa-rotate-90")) {
        $('#' + cardID).parent().find("h2").find("span").find("i").removeClass("fa-rotate-90");
		return true;
    } else {
        $('#' + cardID).parent().find("h2").find("span").find("i").addClass("fa-rotate-90");
		return false;
    }
};

function escapeHTML(str){
	return $('<div>').text(str).html();
}