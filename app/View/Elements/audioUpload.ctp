<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

    echo $this->Form->create(null, array('controller' => 'audio_files',
                                   'action' => 'upload',
                                   'enctype' => 'multipart/form-data'));
    // 100 Mbytes (roughly)
    echo '<input type="hidden" name="MAX_FILE_SIZE" value="100000000"/>';
    echo $this->Form->hidden('AudioFile.patient_id',
                       array('value' => $patientId));
    echo $this->Form->hidden(AppController::CAKEID_KEY,
                       array(
                           'value' => $this->Session->read(AppController::ID_KEY)
                      ));
    echo '<label for = \"uploadedFile\">Choose a file:</label>';
    echo $this->Form->file('AudioFile.uploadedfile');

    if ($initialUpload) {
        echo '<table class="viewone"><tr><th>Check if no recording made:</th><td>';
        echo $this->Form->input('AudioFile.no_recording_flag', 
	                  array('type' => 'checkbox',
			        'label' => ''));
        echo '</td></tr></table>';
    } else if ($confirm) {
        echo '<table class="viewone"><tr><th>Confirm re-upload:</th><td>';
        echo $this->Form->input('AudioFile.confirm', 
	                  array('type' => 'checkbox',
			        'label' => ''));
        echo '</td></tr></table>';
    }
    
    echo $this->Form->submit('Upload');
    echo $this->Form->end();
?>

