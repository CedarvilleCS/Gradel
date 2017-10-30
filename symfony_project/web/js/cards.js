function collapseCard(cardID) {
    console.log("close card " + cardID);

    isOpen = true;
    
    $('#' + cardID).children().slideToggle();
    
    // if($('#' + cardID).is(':hidden')) {
    //     console.log("isOpen");
    //     $("#cardID").html("test");
    //     isOpen = false;
    // }
    // $(".btn-close").show()
};