<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<h3>Notes</h3>
<div style="margin: -10px 0 10px 0">These notes are for internal reference.</div>

<div id="notesContainer">
    <?php
    if (empty($notes)) {
    ?>
        <div class="well note">No notes</div>
    <?php
    } else {

        foreach($notes as $note) { 
            $authorId = $note['author_id'];
            $authorName = $staffs[$authorId];
            $flagTypeCurrent = isset($note['flag_type']) ? explode(',', $note['flag_type']) : '';

            // TODO - Move to controller or, better, call from database
            $flagType = '<div>';
            $flagOptions = array("Identifiers in note","Participant distress","Participant feedback","Provider feedback","Technical issue","Other");
            $flagOptionsLength = count($flagOptions);
            for($x=0;$x<$flagOptionsLength;$x++)  {
                $checkedFlag = (in_array($flagOptions[$x], $flagTypeCurrent)) ? 'checked' : '';
                $flagType .= '<label class="checkbox inline">
                    <input type="checkbox" class="note-flag" name="flagType'.$note['id'].'" value="'.$flagOptions[$x].'" '.$checkedFlag.'>'.$flagOptions[$x].'
                </label>';
            }
            $flagType .= '</div>';
            echo "<div class='well note' id='{$note['id']}'>
                <p>".Sanitize::html($note['text'])."</p>
                <div><small>Author: $authorName | Created: ";
                echo date("n/j/Y H:i",strtotime($note['created']));
                echo '</small></div>'.$flagType.'
            </div>';
        } //foreach
    }
    ?>
</div>
<?php
if ($showPatientViewNotes) {
?>
<br /> 
<h3>
Notes for the Patient
</h3>

<div style="margin: -10px 0 10px 0">The most recent note will appear on the patient's homepage when they log in.</div>
    
<table class="table table-bordered table-condensed">
<?php
    if (empty($patientViewNotes)) {
?>
        <tr><td>No patient view notes</td></tr>
<?php
    } else {
        foreach($patientViewNotes as $note) { 
            echo "<tr class='pvnote' id='{$note['id']}'>
                    <td>
                      <div class='pvdiv'>{$note['text']}</div>
                      <div class='pvnotefooter'>
                        <input type='submit' class='submit' value='Save' style='display:none;'/>
                        <span class='author-info'>Author: {$note['author_id']} |  
                        Last modified:" . substr($note['lastmod'], 0, -3) ."</span>";
                        if ($editPatientViewNotes) {
                            echo " <span class='edit-tool'>| <a href='#' class='edit'>Edit Note</a></span> | <a href='#' class='delete'>Delete Note</a>";
                        }
						echo "
                      </div>
    	  	    </td>
	      	</tr>";
        }
    }
?>
</table>
<?php
} // if ($showPatientViewNotes) {
?>
        <button class="btn add-element-btn" id="addNoteBtn">Add New Note</button>
        <?php
        echo $this->Form->create('addNote', array(
            'class' => 'form-condensed hide',
            'action' => 'edit'
        ));
        ?>
        <div class="well admin-edit-section note" id="noteAdd">
            <?php
            echo "<label for='newNote' class='control-label'>New Note</label>";
            echo $this->Form->textarea('notes', array('rows' => 5, 'class' => 'span6'));
            // TODO - Switch to cake form input
            $flagType = '<div>';
            $flagOptions = array("Identifiers in note","Participant distress","Participant feedback","Provider feedback","Technical issue","Other");
            $flagOptionsLength = count($flagOptions);
            for($x=0;$x<$flagOptionsLength;$x++)  {
                $flagType .= '<label class="checkbox inline">
                    <input type="checkbox" name="newNoteFlag" value="'.$flagOptions[$x].'">'.$flagOptions[$x].'
                </label>';
            }
            $flagType .= '</div>';
            echo $flagType;
            echo '<div style="margin-top: 12px">';
            echo $this->Form->submit("Add Note", array(
                "class"=>"btn btn-primary",
                "div"=>false,
                "id"=>"submitNewNote"
            ));
            echo $this->Form->button("Cancel", array(
                "class"=>"btn btn-small cancel-element-btn",
                "style"=>array('margin-left:20px'),
                "div"=>false,
                "id"=>"cancelNewNote"
            ));
            echo '</div>';
            ?>
        </div>
        <?php echo $this->Form->end(); ?>

        <br /><br /><br />

<script>
$(document).ready(function() {
    $("tr.pvnote a.delete").click(function() {
        if (!confirm("Do you want to delete this note permanently?")) {
            return false;
        }

        $.post("<?php echo $baseUrl; ?>" + "deleteNote", {
            "data[PatientViewNote][id]" : $(this).parents('tr').attr('id'),
            "data[AppController][AppController_id]" : acidValue
        }); 

        $(this).parents("tr").html("<td>No patient view notes</td>");
        return false;
    });

    $("tr.pvnote a.edit").click(function() {
        var $row = $(this).parents("tr"),
            $div = $row.find("div.pvdiv"),
            text = $div.text(),
            $footer = $row.find("div.pvnotefooter");
            $node = $("<textarea/>").html(text).addClass("text");

        $("span.edit-tool", $row).hide();
        $div.html($node);
        $node.focus();
        $("input.submit", $row).show();
        return false;
    });

    $("tr.pvnote input.submit").click(function() {
        var $row = $(this).parents("tr"),
            $div = $row.find("div.pvdiv"),
            $text = $div.find(".text").val();
            $footer = $row.find("div.pvnotefooter"),
            $author = $row.find("span.author-info");

        $.post("<?php echo $baseUrl; ?>" + "editNote", {
                "data[PatientViewNote][id]"   : $row.attr('id'),
                "data[PatientViewNote][text]" : $text,
                "data[AppController][AppController_id]" : acidValue
        });

        $(this).hide();
        $div.html($text);
        $("span.edit-tool", $row).show();
        $($author).replaceWith("Author: <?php echo $authUser; ?> | Last modified: " +
                     "<?php date_default_timezone_set($timezone); 
                            echo date('Y-m-d H:i');?>");
        return false;
    });

    // Add new note inline via ajax
    function setNote(noteText, noteFlag, staffId) {
        $.ajax ({
            type: "POST", // since we're creating a new record
            url: appRoot + 'patients/addStaffNote.json',
            dataType: 'json',
            async: false,
            data: {
                "data[AppController][AppController_id]" : acidValue,
                "data[Note][patient_id]" : "<?php echo $patientId ?>",
                "data[Note][author_id]" : staffId,
                "data[Note][text]" : noteText,
                "data[Note][flag_type]" : noteFlag
            },
            success: function () {
                location.reload(true);
                /*** Possible alternate to load note element only
                $("#cancelNewNote").click();
                $('#notesTable').fadeOut('slow', function () {
                    $('#notesContainer').load("edit #notesTable", function() {
                        $('#notesTable').fadeIn('slow');
                    });
                }); ***/
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                alert ('Sorry, this note can\'t be created: ' + responseTxt.message);
            }
        });
    }

    // Function for AJAX saving of notes
    $('#submitNewNote').click(function(){
        var noteText = $("#addNoteNotes").val();
        var noteFlag = "";
        $("input[name=newNoteFlag]:checked").each(function(){
           noteFlag += $(this).val() + ","; 
        });
        // Remove trailing comma
        noteFlag = noteFlag.substring(0, noteFlag.length - 1);
        var staffId = <?= $authd_user_id ?>;
        setNote(noteText, noteFlag, staffId);
        return false; 
    });
    
    // Update flag for existing note
    function updateNoteFlag(noteId, noteFlag) {
        $.ajax ({
            type: "POST", // since we're creating a new record
            url: appRoot + 'patients/flagStaffNote.json',
            dataType: 'json',
            async: false,
            data: {
                "data[AppController][AppController_id]" : acidValue,
                "data[Note][id]" : noteId,
                "data[Note][flag_type]" : noteFlag
            },
            error: function (jqXHR, textStatus, errorThrown) {
                var responseTxt = jQuery.parseJSON(jqXHR.responseText);
                alert ('Sorry, this flag can\'t be created: ' + responseTxt.message);
            }
        });
    }

    // Trigger note flag update
    $('.note-flag').on("click",function(){
        var noteName = $(this).attr('name');
        var noteFlag = "";
        $("input[name="+noteName+"]:checked").each(function(){
           noteFlag += $(this).val() + ","; 
        });
        if (noteFlag != "") {
            noteFlag = noteFlag.substring(0, noteFlag.length - 1);
        }
        var noteId = $(this).attr('name').replace('flagType','');
        updateNoteFlag(noteId, noteFlag);
    });

}); // ready
</script>

