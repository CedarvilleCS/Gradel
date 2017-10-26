function collapseCard(cardID) {
    console.log("close card " + cardID);
    $('#' + cardID).children().slideToggle();
    // $(".btn-close").show()
};