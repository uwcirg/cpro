<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<?php

    // Check to see if patient already has language set. Default to 
    // English if none.
    if (isset($patientLocale) && $patientLocale == "es_MX") {
        $patientLang = "Spanish";
        $altLang = "English";
        $altLangId = "en_US";
    } else {
        $patientLang = "English";
        $altLang = "Spanish";
        $altLangId = "es_MX";
    }
?>
    <h3>Language Preference</h3>
    <p>Patient's language is currently set to: <strong><span id="currentLang"><?php echo $patientLang ?></span></strong></p>
    <button type="button" id="<? echo $altLangId ?>" class="btn btn-small lang-changer" style="margin-left: 20px">Change to <span><? echo $altLang ?></span></button>
    <br />
            
<script>

$(document).ready(function(){

/*** Functions for changing user language ***/
    // Set current language choice (defaults to English)
    // AJAX to change language for this user
    function changeLanguage(button) {
        var lang = $(button).attr('id');
        var langName = $(button).find("span").html();
        var altName = $("#currentLang").html();
        $('#changedCheck').remove();
        $("#currentLang").fadeOut('fast');
        $(".lang-changer").animate({opacity: 0}, 200);
        $.ajax ({
            type: "POST",
            url: appRoot + 'patients/setLanguage',
            dataType: 'json',
            // Sends locale and acidValue
            data: {"data[User][locale]" : lang , "data[AppController][AppController_id]" : acidValue, "data[User][id]" : '<?php echo $patientId ?>'}
        }).done(function(){
            $("#currentLang").text(langName);
            $(".lang-changer span").text(altName).fadeIn('slow');
            $("#currentLang").delay(500).fadeIn('slow', function(){
                $(this).after(' <i id="changedCheck" class="icon-ok icon-green"></i>');                    
            });
            $(".lang-changer").delay(1000).animate({opacity: 1.0}, 'slow');
        }).fail(function(){
            formErrorAlert("There was a problem updating the language.");
            $("#currentLang").fadeIn('fast');
            $(".lang-changer").animate({opacity: 1}, 200);
        });
    }
    $('button.lang-changer').click(function(){
        changeLanguage(this);
    }); // Functions for changing user language

}); // ready

</script>
