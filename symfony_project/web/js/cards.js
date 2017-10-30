function collapseCard(cardID) {
    console.log("close card " + cardID);

    isOpen = true;
    
    $('#' + cardID).children().slideToggle();
};