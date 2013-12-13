/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/   
 
$(document).ready(function() {
		
    /* Init DataTables */
    var oTable = $('#diarytable-edit').dataTable( {
        "bSort": false,
        "bJQueryUI": false,
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false,
        "bPaginate": false
    } );


    // Set variable to look for "-" in ID strings.
    var parseID = /\b-+\b/;

    // If value is "Error" then immediately remove on click. This is used in 
    // validation of steps and duration below (which are num value only). 
    // A convenience to the user (they don't have to delete "error")
    $('td.edit-entry').click(function() {
        var editValue = $(this).html();
        if ( editValue == 'Error') {
            $(this).html('');
        }
    });
    
    // Function to allow user to tab through fields. Only works with <td>s
    // that don't contain <div>s
    $('td.edit-entry').bind('keydown', function(evt) {
        if(evt.keyCode==9) {
            $(this).find("input, select").blur();
            var $this = $(this);
            // Get the index of the td.
            var cellIndex = $this.index();
            // Find cell in the next row that has the same index.
            $this.closest('tr').next().children().eq(cellIndex).click();
            //Suppress normal tab
            return false;
        };
    });

    /* Apply the jEditable handlers to the table - Fatigue */
    $('td.fatigue-edit', oTable.fnGetNodes()).editable( editActionWSiteRoot, {
        placeholder: 'Add...',
        cssclass: 'diary-edit',
        onblur: 'submit',
        data: "{'0':'0 - None','1':'1','2':'2','3':'3','4':'4','5':'5','6':'6','7':'7','8':'8','9':'9','10':'10 - Very'}",
        type: 'select',
        // value is the old text
        'submitdata': function(value, settings) {
            input = $(this).find('select'); // select
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][fatigue]" : mostRecentMod // passes input value
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created.
                    "data[ActivityDiaryEntry][fatigue]" : mostRecentMod // passes input value
                };
            }
        },
        'callback': function(jsonResult, y){
            // alert('jsonResult returned from test edit: result = ' + jsonResult.result + "; resultDesc = " + jsonResult.resultDesc);
            if ( mostRecentMod != "Add..." && mostRecentMod != "" ) {
                $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
                $('#results-data').fadeOut('slow', function () {
                    $('#results-data').load("activity_diaries #results-data");
                    $('#results-data').fadeIn('slow');
                });
				$('#f' + cleanId).removeClass('to-add');
            }
        }
    } );
						
    /* Apply the jEditable handlers to the table - Activity */
    $('.activity-edit', oTable.fnGetNodes()).editable( editActionWSiteRoot, {
        placeholder: 'Add...',
        cssclass: 'diary-edit',
        onblur: 'submit',
        data: " {'Walking':'Walking','Biking':'Biking','Running':'Running','Other':'Other:','None':'None'}",
        type: 'select',
        // value is the old text
        'submitdata': function(value, settings) {
            input = $(this).find('select'); // input or select
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][type]" : mostRecentMod // passes input value
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    // "data[ActivityDiaryEntry][date]" : '2011-03-03', // like '1999-12-31'. Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created.
                    "data[ActivityDiaryEntry][type]" : mostRecentMod // passes input value
                };
            }
        },
        'callback': function(jsonResult, y){
            if ( mostRecentMod != "Add..." && mostRecentMod != "" ) {
                $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
                $('#results-data').fadeOut('slow', function () {
                    $('#results-data').load("activity_diaries #results-data");
                    $('#results-data').fadeIn('slow');
                });
                $('#a' + cleanId).parent().removeClass('to-add');
            }
        }
    } );

    /* Apply the jEditable handlers to the table - Other Activity */
    $('.activity-other-edit', oTable.fnGetNodes()).editable( editActionWSiteRoot, {
        placeholder: '<em>Specify...</em>',
        cssclass: 'diary-edit',
        onblur: 'submit',
        // value is the old text
        'submitdata': function(value, settings) {
            var input = $(this).find('input'); // input or select
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][typeOther]" : mostRecentMod // passes input value
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created. Testing with a set record ID.
                    "data[ActivityDiaryEntry][typeOther]" : mostRecentMod // passes input value
                };
            }
        },
        'callback': function(jsonResult, y){
            if ( mostRecentMod != "Specify..." && mostRecentMod != "" ) {
                $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
            }
        }
    } );

    /* Apply the jEditable handlers to the table - Duration */
    $('td.duration-edit', oTable.fnGetNodes()).editable( editActionWSiteRoot, {
        placeholder: 'Add...',
        cssclass: 'diary-edit',
        onblur: 'submit',
        // value is the old text
        'submitdata': function(value, settings) {
            var input = $(this).find('input'); // input or select
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][minutes]" : mostRecentMod // passes input value
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created. Testing with a set record ID.
                    "data[ActivityDiaryEntry][minutes]" : mostRecentMod // passes input value
                };
            }
        },
        'callback': function(jsonResult, y){
            if ( jsonResult == "Failed to save" ) {
                // If validation fails highlight the error and add show tooltip
                $('#d' + cleanId).html('Error').addClass('to-add').attr('rel', 'tooltip').attr('title', 'Must be a number. Click on the box to try again.');
                $("#d" + cleanId + "[rel=tooltip]").tooltip('show');
            } else {
                // Otherwise update results-data and remove tooltip and "to-add" class
                $("#d" + cleanId + "[rel=tooltip]").tooltip('destroy');
                $(this).val = mostRecentMod;
                if ( mostRecentMod != "Add..." && mostRecentMod != "" ) {
                    $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
                    $('#results-data').fadeOut('slow', function () {
                        $('#results-data').load("activity_diaries #results-data");
                        $('#results-data').fadeIn('slow');
                    });
                    $('#d' + cleanId).removeClass('to-add');
                }
            }
        }
    } );
	

    /* Apply the jEditable handlers to the table - Steps */
    $('td.step-edit', oTable.fnGetNodes()).editable( editActionWSiteRoot, {
        placeholder: 'Add...',
        cssclass: 'diary-edit',
        onblur: 'submit',
        // value is the old text
        'submitdata': function(value, settings) {
            var input = $(this).find('input'); // input or select
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][steps]" : mostRecentMod // passes stepValue
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created. Testing with a set record ID.
                    "data[ActivityDiaryEntry][steps]" : mostRecentMod // passes stepValue
                };
            }
        },
        'callback': function(jsonResult, y){
            if ( jsonResult == "Failed to save" ) {
                // If validation fails highlight the error and add show tooltip
                $('#s' + cleanId).html('Error').addClass('to-add').attr('rel', 'tooltip').attr('title', 'Must be a number. Click on the box to try again.');
                $("#s" + cleanId + "[rel=tooltip]").tooltip('show');
            } else {
                // Otherwise update results-data and remove tooltip and "to-add" class
                $("#s" + cleanId + "[rel=tooltip]").tooltip('destroy');
                if ( mostRecentMod != "Add..." && mostRecentMod != "" ) {
                    $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
                    $('#results-data').fadeOut('slow', function () {
                        $('#results-data').load("activity_diaries #results-data");
                        $('#results-data').fadeIn('slow');
                    });
                    $('#s' + cleanId).removeClass('to-add');
                }                
            }
        }
    } );

    /* Apply the jEditable handlers to the table - Notes2 */
    $('.notes-dialog-edit').editable( editActionWSiteRoot, {
        placeholder: '<em>Add a new note here...</em>',
        event: 'mouseover',
        onblur: 'submit',
        cssclass: 'diary-edit',
        width: '95%',
        type: 'textarea',
        rows: '6',
        // value is the old text
        'submitdata': function(value, settings) {
            var input = $(this).find('textarea'); // note is a textarea
            mostRecentMod = input.val();
            cleanId = this.getAttribute('id').substr(1);
            cleanTitle = this.getAttribute('title').substr(2);
            if ( cleanId.match(parseID) ) {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][date]" : cleanId, // Required only if creating a new record. Don't change an existing record's date.
                    "data[ActivityDiaryEntry][note]" : mostRecentMod // passes input value
                };
            } else {
                return {
                    "data[AppController][AppController_id]" : appControllerId, // REQUIRED helps prevent cross-site scripting
                    "data[ActivityDiaryEntry][id]" : cleanId, // Include this to update a record. If you don't include this, a new record will be created. Testing with a set record ID.
                    "data[ActivityDiaryEntry][note]" : mostRecentMod // passes input value
                };
            }
        },
        'callback': function(jsonResult, y){
            $("#nd" +  cleanTitle).modal('hide');
            if ( mostRecentMod != "Add..." && mostRecentMod != "" ) {
                $('#saving').fadeIn('fast').delay('1000').fadeOut('slow');
                // Updates the truncated notes field in the table
                $('#notes-reload td#n' + cleanTitle + '').fadeTo('slow', 0.5, function () {
                    $('#notes-reload td#n' + cleanTitle + '').load("activity_diaries td#n" + cleanTitle + " span", function () {
                        $('#notes-reload td#n' + cleanTitle + '').fadeTo('slow', 1);
                    });
                });
            }
        }
    } );

    // Show/hides typeOther when "Other" is selected from the Activity Type
    $("form.diary-edit select").live("change", function(){
        $(this).val() === "Other"
        ? $(this).closest('div').next().show()
        : $(this).closest('div').next().hide();
        $(this).val() != "Other"
        ? $(this).closest('div').next().hide()
        : $(this).closest('div').next().show();
    });

    // Opens notes modal upon click
    for (var clickBox=0;clickBox<7;clickBox++) { 
        $("#n" + clickBox).click(function() {
            var boxId = $(this).attr('id') + 'box';
            $("#" + boxId).modal('show');
        });       
    }

    // function for immediately updating select dropdowns without having to click elsewhere
    $('.activity-edit select, .fatigue-edit select').live('change', function () {
        $(this).parent().submit();
    });
    
} );
