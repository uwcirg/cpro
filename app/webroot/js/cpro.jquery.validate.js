/**
* User related rules for dhair jquery form validation
* These are designed for compatibility w/ cakephp
* Be sure to include jquery.validate.js
* Note: we now use html5 'required' attribute instead of specifying it here
* Also, there have been some weird issues with IE8 and using the required 
* attribute. It seems to 
*/

// Sets all rules for validation.
var userRelatedRules = {
    "data[User][first_name]": {
        //required: true
    },
    "data[User][last_name]": {
        //required: true
    },
    "data[Patient][birthdate]": {
        // IE8 had problems when date was true. Use dateISO instead and specify
        // that date is false.
        date: true
        //dateISO: true
        //required: false
    },
    "data[Patient][check_again_date]": {
        // IE8 had problems when date was true. Use dateISO instead and specify
        // that date is false.
        date: true
        //dateISO: true
    }, 
    "data[Patient][consent_date]": {
        date: true
    }, 
    "data[t1][date]": {
        //required: true,
        //dateISO: true
    },
    "data[Appointment][0][date]": {
        // IE8 had problems when date was true. Use dateISO instead and specify
        // that date is false.
        date: true,
        dateISO: false
    },
    "data[Appointment][0][hour][hour]": {
        //required: false
        required: {
            depends: function(element){
                return $("#AppointmentDate").val()!=""
            }
        }
    },
    "data[Appointment][0][minute][min]": {
        //required: false
        required: {
            depends: function(element){
                return $("#Appointment0HourHour").val()!=""
            }
        }
    },
    "data[Patient][MRN]": {
        //required: true
    },
    "data[User][username]": {
        minlength: 2/**,
        required: false*/
    },
    "data[User][password]": {
        //required: true
    },
    "data[User][password_confirm]": {
        equalTo: "#data\\[User\\]\\[password\\]"/**,
        required: true*/
    }, 
    "data[User][email]": {
        email: true
    },
    "data[User][email_confirm]": {
        equalTo: "#data\\[User\\]\\[email\\]"/**,
        required: true*/
    },
    //"data[Patient][secret_phrase]": "required",
    "data[Clinician][first_name]": {
        //required: true
    },
    "data[Clinician][last_name]": {
        //required: true
    },
    "data[Clinician][email]": {
        email: true
    }
}

// To replace default error message, which is "This field is required."
var userRelatedMessages = {
    "data[User][first_name]": "First name required.",
    "data[User][last_name]": "Last name required.",
    "data[User][username]": { required: "Username is required.", minlength: jQuery.format("Usernames must be at least {0} characters")},
    "data[Patient][birthdate]": { required: "Birthday is required.", date: "Birthday must be a date." },
    "data[User][password_confirm]": "Please enter the same password as above.",
    "data[Patient][check_again_date]": { required: "Check again required.", date: "Invalid date. Must be formatted mm/dd/yyyy" },
    "data[Patient][consent_date]": { date: "Invalid date. Must be formatted mm/dd/yyyy" },
    "data[Appointment][0][hour][hour]": "Select time for appointment.",
    "data[Appointment][0][minute][min]": "Select time for appointment."
}

// Set error message groups (when more than one field per line)
var userRelatedGroups = {
    appointment: "data[Appointment][0][date] data[Appointment][0][hour][hour] data[Appointment][0][minute][min]"
}

// Removes generic help to select time for appointment before submit since the
// validate with specifiy the same message in red.
function appointDateGroup(){
    $("#AppointmentDate").parent().find("span.help-inline").remove();
}
// Function to run validation
function validatePatientForm(formName) {
    $(formName).validate({
        rules: userRelatedRules,
        messages: userRelatedMessages,
        groups: userRelatedGroups,
        onkeyup: false,
        showErrors: function(errorMap, errorList) {
            this.defaultShowErrors();
            // To add Bootstrap class to error message
            $(this.currentForm).find('label.error').addClass('help-inline');
        },
        // Toggles highlight of invalid fields.
        highlight: function(element) {
            $(element).closest('.control-group').addClass('error');
        },
        unhighlight: function(element) {
            $(element).closest('.control-group').removeClass('error');
        },
        // Creates one error message when fields are grouped together
        errorPlacement: function(error, element) {
            if (element.attr("name") == "data[Appointment][0][date]" 
                || element.attr("name") == "data[Appointment][0][hour][hour]"
                || element.attr("name") == "data[Appointment][0][minute][min]" ) {
                    error.insertAfter("#Appointment0MinuteMin");
                    appointDateGroup();
                } 
            else 
                error.insertAfter(element);
        }
    });
};
