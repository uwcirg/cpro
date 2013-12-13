/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * Used for inline editing via the jquery.jeditable.js plugin and passed
    * to server via ajax posts. Intended to give partners the ability to directly
    * edit text used in survey and interventions - initially launching for
    * P3P Mazzone.
    * 
    * Currently implemented on surveys/summary and p3p/overview
    * 
    * TODO - Consider using x-editable which ties in better with Bootstrap and
    * has baked in WYSIWYG functionality.
*/   
 
$(document).ready(function() {
    
    /*** Basic elements used by all editables - allow for undoing saves and 
     * function to post to server. Need to pass the proper URL.
     ***/
		
    // Global value holder that will contain most recent change
    oldValueHolder = '';
    // If any editable is clicked, remove any existing undo button and attribute
    // from associated span
    $('.editable').click(function(){
        $("#wasEdited").removeAttr("id");
        $("#dataUndoButton").remove();
        // Disable preview button while editing
        $(".editor-preview").addClass('disabled').attr('disabled', true);
    });
    // Shotcut to scoll down to next page in P3P editor
    $('.go-down').click(function(){
        var $foo = $('button.go-down');
        var idx = $foo.index(this);
        var next = $foo.eq(idx + 1);
        $('html,body').animate({scrollTop: next.offset().top - 50});
    });
    // After load remove the _es_MX from the css class names. Makes for less
    // classes to deal with. Needed for P3P overview
    $("span.edit-item_name_es_MX").removeClass('edit-item_name_es_MX').addClass('edit-item_name');
    $("span.edit-intervention_text_es_MX").removeClass('edit-intervention_text_es_MX').addClass('edit-intervention_text');
    $("span.edit-label_es_MX").removeClass('edit-label_es_MX').addClass('edit-label');
    
    // Undo function - gets value of the associated span and then passes them to
    // the function along with the revert status
    $('#dataUndoButton').live('click', function(){
        if(confirm("Are you sure you want to undo the most recent edit?")) {
            var urlParams = $(this).attr("data-undo-param");
            var dbId = $("#wasEdited").attr("data-db_id");
            var dbTable = $("#wasEdited").attr("data-db_table");
            var dbCol = $("#wasEdited").attr("data-db_col");
            var revert = 'yes';
            // Change the value on the frontend
            $('#wasEdited').html(oldValueHolder);
            // Remove the Undo button
            $(this).fadeOut();
            // Pass undo to database. Use the undo value for both old and new values
            submitEdit(oldValueHolder, oldValueHolder, dbId, dbTable, dbCol, revert, urlParams)           
        };
    });
    // Main function to submit edits to database
    function submitEdit(value, origValue, dbId, dbTable, dbCol, revert, urlParams) {
        // In order to pass variables as property names, need to create a data
        // array.
        var secureIt = "data[AppController][AppController_id]";
        var toUpdate = "data[" + dbTable + "][" + dbCol + "]";
        var data = {};
        data[secureIt] = acidValue;
        // value is passed from jeditable
        data[toUpdate] = value;
        $.ajax ({
            type: "PUT",
            // url needs to be set per instance
            url: appRoot + urlParams + dbId + '.json',
            dataType: 'json',
            data: data,
            error: function() {
                alert('There was a problem submitting this update.');
            },
            success: function() {
                // If successfully saved and not from the undo function, then
                // add the "undo" button.
                if (revert == 'no') {
                    oldValueHolder = origValue;
                    $('span[data-db_id="'+dbId+'"][data-db_col="'+dbCol+'"]').attr('id','wasEdited').after('<button class="btn btn-small" style="margin-left: 10px" id="dataUndoButton" data-undo-param="'+urlParams+'" title="Undo most recent edit.">Undo Edit</button>');
                }
                // Enable preview buttons (were disabled during edit)
                $(".editor-preview").removeClass('disabled').removeAttr('disabled');
            }
        });
    }
    function cancelEdit() {
        $(".editor-preview").removeClass('disabled').removeAttr('disabled');
    }
    /*** /End - Basic elements used by all editables ***/
    
    
    /*** Functions used by p3p/overview - uses 'p3p/edit/' for urlParams ***/
    $('.editable.edit-intervention_text').editable(function(value, settings) { 
        // Used to set location of ajax post url
        var urlParams = 'p3p/edit/';
        var origValue = this.revert;
        // Get attributes from span
        var dbId = $(this).attr("data-db_id");
        var dbTable = $(this).attr("data-db_table");
        var dbCol = $(this).attr("data-db_col");
        var revert = 'no';
        submitEdit(value, origValue, dbId, dbTable, dbCol, revert, urlParams);
        // Needed so that value is correctedly displayed with jeditable on page
        return(value);
     }, {
        type : "textarea",
        submit : "OK",
        cancel : "Cancel",
        tooltip : "Click to edit...",
        onblur : "ignore",
        onreset : cancelEdit
    });
    // Input edits for label and sequence
    $('.editable.edit-label, .editable.edit-Sequence').editable(function(value, settings) {
        // Used to set location of ajax post url
        var urlParams = 'p3p/edit/';
        var origValue = this.revert;
        // Get attributes from span
        var dbId = $(this).attr("data-db_id");
        var dbTable = $(this).attr("data-db_table");
        var dbCol = $(this).attr("data-db_col");
        var revert = 'no';
        submitEdit(value, origValue, dbId, dbTable, dbCol, revert, urlParams);
        // Needed so that value is correctedly displayed with jeditable on page
        return(value);
     }, {
        type : "text",
        submit : "OK",
        cancel : "Cancel",
        tooltip : "Click to edit...",
        onblur : "ignore",
        onreset : cancelEdit
    });
    /*** /End - Functions used by p3p/overview ***/
    
    /*** Functions used by surveys/summary - uses 'surveys/' for urlParams ***/    
    $('.editable.edit-Title, .editable.edit-Header, .editable.edit-option').editable(function(value, settings) {
        // Used to set location of ajax post url
        var urlParams = 'surveys/';
        var origValue = this.revert;
        // Get attributes from span
        var dbId = $(this).attr("data-db_id");
        var dbTable = $(this).attr("data-db_table");
        var dbCol = $(this).attr("data-db_col");
        var revert = 'no';
        submitEdit(value, origValue, dbId, dbTable, dbCol, revert, urlParams);
        // Needed so that value is correctedly displayed with jeditable on page
        return(value);
     }, { 
        type : 'text',
        submit : "OK",
        cancel : "Cancel",
        tooltip : "Click to edit...",
        onblur : "ignore",
        onreset : cancelEdit
    });
    $('.editable.edit-question, .editable.edit-page').editable(function(value, settings) {
        // Used to set location of ajax post url
        var urlParams = 'surveys/';
        var origValue = this.revert;
        // Get attributes from span
        var dbId = $(this).attr("data-db_id");
        var dbTable = $(this).attr("data-db_table");
        var dbCol = $(this).attr("data-db_col");
        var revert = 'no';
        submitEdit(value, origValue, dbId, dbTable, dbCol, revert, urlParams);
        // Needed so that value is correctedly displayed with jeditable on page
        return(value);
     }, {
        type : 'textarea',
        submit : "OK",
        cancel : "Cancel",
        tooltip : "Click to edit...",
        onblur : "ignore",
        onreset : cancelEdit
    });
    /*** /End - Functions used by surveys/summary ***/  
    
});
