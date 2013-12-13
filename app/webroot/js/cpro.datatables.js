/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/   

$(document).ready(function() {
    // Adds classes for use with Bootstrap
    $.extend( $.fn.dataTableExt.oStdClasses, {
        "sWrapper": "dataTables_wrapper form-inline"
    });
    // Sets standard defaults for our use of Datatables. Can be overridden 
    // per table
    $.extend( true, $.fn.dataTable.defaults, {
        "bPaginate": false,
        "oLanguage": {
            "sInfoEmpty": "No matching records found",
            "sInfoFiltered": "(filtered from _MAX_)"
        }
    });
    
    // Allow for clicking on entire table row to view
    $('.patient-datatable tr td').on('click', function() {
        var patientId = $(this).parent().find("td.patient-id").text();
        if(patientId) {
            var href = appRoot+"patients/edit/"+patientId;
            window.location = href;
        }
    });
    /* Alternate way to add links to cells in <td> which creates actual link
    $('#view-all-table tr').each(function() {
        var href = $(this).find("a").attr("href");
        $(this).children('td').not(':first').wrapInner('<a href="#">');
    });*/
    
    // Admin - Appointment Calendar Table
    $('#appointment-table').dataTable({
        "sDom": "<'row'<'span5'i><'span5'f>r>t",
        "oLanguage": {
            "sInfo": "_TOTAL_ appointment(s) this week"
        },
        // Set default sorting and then associate visible data column with 
        // hidden one. Visible is "Day mm/dd". Hidden is "yyyy-mm-dd"
        "aaSorting": [[3,'asc']],
        "aoColumnDefs": [
            {"iDataSort": 4, "aTargets": [3]},
            {"bVisible": false, "aTargets": [4]}
        ]  
    }); 
    
    // Admin - View All Patients Table
    $('#view-all-table').dataTable({
        "aaSorting": [[2,'asc']],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>",
        // Make sure date columns are sorted properly
        "aoColumnDefs": [
            { "sType": "date", "aTargets": [ 5,8 ] }
        ],
        "oLanguage": {
            "sInfo": "_TOTAL_ patients in system"
        }
    }); 

    // Admin - Check Agains Table
    $('#check-again-cal-table').dataTable( {
        "sDom": "<'row'<'span5'i><'span5'f>r>t",
        "oLanguage": {
            "sInfo": "_TOTAL_ check again(s) this week"
        },
        // Set default sorting and then associate visible data column with 
        // hidden one. Visible is "Day mm/dd". Hidden is "yyyy-mm-dd"
        "aaSorting": [[3,'asc']],
        "aoColumnDefs": [
            {"iDataSort": 4, "aTargets": [3]},
            {"bVisible": false, "aTargets": [4]}
        ]        
    }); 

    // Admin - Past Check Agains Table
    $('#no-check-again-table').dataTable( {
        "bAutoWidth": false,
//        "aoColumnDefs": [
//            { "sWidth": "350px", "aTargets": [ 5 ] }
//        ],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>",
        "oLanguage": {
            "sInfo": "_TOTAL_ matching patients"
        },
        // Set default sorting and then associate visible data column with 
        // hidden one. Visible is "Day mm/dd". Hidden is "yyyy-mm-dd"
        "aaSorting": [[3,'asc']],
        "aoColumnDefs": [
            {"iDataSort": 4, "aTargets": [3]},
            {"bVisible": false, "aTargets": [4]}
        ]
    }); 
    
    // Admin - One Month FU Table
    $('#one-month-table').dataTable( {
        "bAutoWidth": false,
        "aaSorting": [[3,'asc']],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>",
        "oLanguage": {
            "sInfo": "_TOTAL_ matching patients"
        }
    });     

    // Admin - One Week FU Table
    $('#one-week-table').dataTable( {
        "bAutoWidth": false,
        "aaSorting": [[4,'asc']],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>",
        "oLanguage": {
            "sInfo": "_TOTAL_ matching patients"
        }
    });     

    // Admin - Interest Report
    $('#interested-report-table').dataTable( {
        "aaSorting": [[2,'asc']],
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ] }
        ],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>"
    }); 
    
    // Admin - Off-Study Tables
    $('#off-study-table').dataTable( {
        "bAutoWidth": false,
        "aaSorting": [[2,'asc']],
        "aoColumnDefs": [
            { "sWidth": "100px", "aTargets": [ 1,2 ] },
            { "sWidth": "40px", "aTargets": [ 3 ] },
            { "sWidth": "80px", "aTargets": [ 4 ] },
            { "sWidth": "130px", "aTargets": [ 5 ] },
            { "sWidth": "160px", "aTargets": [ 6 ] }
        ],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>"
    }); 

    // Admin - Consents Table
    $('#consents-table').dataTable( {
        "aaSorting": [[4,'asc']],
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 0 ] }
        ],
        "sDom": "<'row'<'span5'i><'span5'f>r>t",
        "oLanguage": {
            "sInfo": "_TOTAL_ patient(s) require consent verification"
        }
    }); 
	
    // Admin - View non-admin user table
    $('#view-nonadmin-users-table').dataTable( {
        "aoColumnDefs": [
            { "bSortable": false, "aTargets": [ 2 ] }
        ],
        "sDom": "<'row'<'span5'i><'span5'f>r>t<'row'<'span10'i>>",
        "oLanguage": {
            "sInfo": "_TOTAL_ matching records"
        }
    }); 
    
    // Admin - Patient Search Results Table
    $('#patient-search-results').dataTable( {
        "aaSorting": [[2,'asc']],
        "bFilter": false,
        "sDom": "<'row'<'span10'i>r>t",
        "oLanguage": {
            "sInfo": "_TOTAL_ patients match this search. To view a patient, click on that row."
        }
    }); 
    
    // Admin - Log Table
    $('#log-table').dataTable( {
        "aaSorting": [[1,'asc']],
        "bProcessing": true,
        "bPaginate": true,
        "iDisplayLength": 50,
        "aoColumnDefs": [
            { "sClass": "center", "aTargets": [ 0, 1, 5 ] }
        ],
        "aLengthMenu": [[50, 100, 250, 1000], [50, 100, 250, "1,000"]]	
    }); 

    // Activity Diary controls (in patients/activityDiary
    $('#diarytable').dataTable( {
        "bSort": false,
        "bJQueryUI": false,
        "bLengthChange": false,
        "bFilter": false,
        "bInfo": false
    }); 

});
