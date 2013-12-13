<?php
/**
    * Share Subscales element
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    Within a larger form, creates controls for determining which
    subscales an associate can see for a patient.

    Args to pass in:
    * $scales: associative array in cakephp format of scales and 
        subscales to display as options
    * $selectedSubscales: array of subscales that are selected. (optional)
    * $a_id: id of the associate, 0 if a new one (optional)
        This should also be a class of the form to let
        jQuery find the right elements
 */
    if(!isset($selectedSubscales))
        $selectedSubscales = array();
    if(!isset($a_id))
        $a_id = 0;
    if(!isset($share_journal))
        $share_journal = 0;
?>
<p><input class="share-all-subscales" id='<?php echo $a_id; ?>-share-all-subscales' type='checkbox'/><label class='share-all' for='<?php echo $a_id; ?>-share-all-subscales'> Share all areas.</label></p>
<div class="spacer"> </div>
<?php
    foreach($scales as $scale) {
    echo "<fieldset><legend>". $scale["Scale"]["name"] . "</legend>\n";
    foreach($scale["Subscale"] as $subscale) {
        echo "<input class='subscale' id='".$a_id.$subscale['id']."' ";
        if(in_array($subscale['id'], $selectedSubscales)) {
            echo "checked='checked' ";
        }
        echo " type='checkbox' name='data[PatientAssociate][Subscale][$subscale[id]]'/>";
        echo "<label class='subscale' for='".$a_id.$subscale['id']."'>" . $subscale['name'] . "</label><br/>\n";
    }
    echo "</fieldset>\n";
} ?>

<fieldset>
    <legend>Journal</legend>
<?php
//echo "<input class='subscale' id='".$a_id."journals' " . 
echo "<input class='journal' id='".$a_id."journal' " . 
    ($share_journal == 1 ? "checked='checked' " : "") . 
    //($share_journals == 1 ? "checked " : "") . 
    "type='checkbox' name='data[PatientAssociate][share_journal]'/>" .
    //"type='checkbox' name='data[PatientAssociate][Subscale][share_journals]'/>" .
    //"<label class='subscale' for='".$a_id."journals'>Journals</label>"
    "<label class='journal' for='".$a_id."journal'>Journal</label>"
?>
<br/>
</fieldset>


<script>
$(function() {
    var form = "form.<?php echo $a_id; ?> ";
    //var form = "form.<?php echo $a_id; ?>";

    $(form + " input.share-all-subscales").click(function() { 
        if($(form + " input.share-all-subscales").is(":checked")) { 
            $(form + " input.subscale").attr('checked', 'checked'); 
            $(form + " input.journal").attr('checked', 'checked'); 
            $(form + " label").addClass('checked');
        } else { 
            $(form + " input.subscale").attr('checked', ''); 
            $(form + " input.journal").attr('checked', ''); 
            $(form + " label").removeClass('checked');
        }
    }); 
     
    $(form + "input.subscale").click(function(input) { 
        if($(this).not(":checked")){ 
            $(form + " input.share-all-subscales").attr('checked', ''); 
            $(form + " label.share-all").removeClass("checked");
        }
    });
    $(form + "input.journal").click(function(input) { 
        if($(this).not(":checked")){ 
            $(form + " input.share-all-subscales").attr('checked', ''); 
            $(form + " label.share-all").removeClass("checked");
        }
    });
});
</script>

