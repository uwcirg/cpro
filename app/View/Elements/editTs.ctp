<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

    foreach ($ts as $t) {
        $lower = strtolower($t);

        if (empty($startedTs[$t]) || $tester) {
	    // allow t-time to be changed for test patients, but give warning
	    if (!empty($startedTs[$t])) {
	        echo "<p>$t survey has been started.</p>";
            }

            echo '<div class="control-group">';
            echo '<label for="' . $lower . 'Date" class="control-label">' . $t . '</label>';
            echo '<div class="controls">';
            
            // if we don't check for empty, empty datetimes turn into 1969-12-31
    	    $dateDefault = empty($timestamps[$lower]) ? '' : 
                                     date('Y-m-d', $timestamps[$lower]);
            $hourDefault = empty($timestamps[$lower]) ? '' : 
	                      date('H', $timestamps[$lower]);
            $minDefault = empty($timestamps[$lower]) ? '' : 
	                     date('i', $timestamps[$lower]);
   
            echo $this->Form->text("{$lower}.date", 
                 array(
                     'default' => $dateDefault,
                     'id' => "{$lower}Date",
                     'class' => 'datep',
                     'style' => 'width: 100px',
                     'label' => "{$lower}Date"
                 ));

            // T1 hour/minutes cannot be false, but others can
            if ($t == 'T1') {
                echo $this->Form->hour("{$lower}.hour", true, $hourDefault,
                    array('default' => $hourDefault,
                        'style' => 'margin-left: 10px; width: auto',
                        'empty' => false));
                echo $this->Form->minute("{$lower}.minute", $minDefault,
                    array('default' => $minDefault,
                        'style' => 'margin-left: 5px; width: auto',
                        'interval' => '5',
                        'empty' => false)); 
            } else {
                echo $this->Form->hour("{$lower}.hour", true, $hourDefault,
                    array('default' => $hourDefault,
                        'style' => 'margin-left: 10px; width: auto',
                        'empty' => false
                    ), 'hh');
                echo $this->Form->minute("{$lower}.minute", $minDefault,
                    array('default' => $minDefault,
                        'style' => 'margin-left: 5px; width: auto',
                        'interval' => '5',
                        'empty' => false), 'mm');
            }
            echo "</div></div>";
        } else {
            echo "$t:  " . $this->request->data['Patient'][$lower] . '<br/>';
            echo "$t survey has been started, and $t time cannot be changed.";
    	}
        if (!$onlyEditDatetimes) {
            echo $this->Form->input("Patient.{$lower}_location");
            echo '<div class="control-group">';
            echo "<label for='Patient{$t}StaffId' class='control-label'>$t Staff</label>";
            echo $this->Form->select("Patient.{$lower}_staff_id", $staffs, 
                $this->request->data['Patient']["{$lower}_staff_id"]);
            echo '</div></div>';
        } // endif
    } // foreach
?>

