/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/   

$(document).ready(function() {
        
    // Disabled main and left nav links that are assigned disabled class
    $('#mainNav li.disabled a').attr('title', 'This section is not yet available.').removeAttr("href");
    $('.intervention-sidebar li.disabled a').attr('title', 'This topic is not yet available.').removeAttr("href");

    // For editor, create modal preview (to interact with popovers)
    $('.editor-preview').click(function() {
        $("#modalBody").empty();
        var previewContent = $(this).parent().next('span').html();
        $("#modalBody").append(previewContent);
        $("#editorPreviewModal").modal('show');
    });
    
});
