function patientEditCallback(element){
    var formElements = [
        "PatientStudyGroup",
        "PatientConsentStatus",
        "PatientConsentDate"
    ];

    // Reload for only the above elements
    if (formElements.indexOf(element.id) == -1)
        return;

    var xhr = $.ajax(document.URL, {
        dataFilter:function(data) {
            var links = $(".patient_tools_links");
            // Iterate over nav bars (should be 2) and replace with current one
            $(data).find(".patient_tools_links").each(function(index) {
                links.eq(index).replaceWith($(this));
            });
            // Update survey window information
            $("#survey_windows").replaceWith($(data).find("#survey_windows"));
        },
        // Disable X-Requested-With:XMLHttpRequest header to prevent triggering isAjax() in AppController
        headers:{'X-Requested-With':{toString: function(){ return ''; }}},
    }, 'html');
}
