$( document ).tooltip({
    track: true,
    content: function(callback) { 
        callback($(this).prop('title').replace(/\\n/g, '<br />')); 
    }
});