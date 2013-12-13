<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<div class="span2 visible-desktop"></div>
<div class="span10">

    <h2>Data Access Tool</h2>

<form action="#" onsubmit="event.returnValue = false; return false;" >

<h4>Project</h4>
<?php
$checked = ' checked';
foreach($projects as $project){

    echo '<input type="radio" name="project" value="' . $project["Project"]["id"] . '"' . $checked . '> ' . $project['Project']['Title'] . '<br/>' . "\n";
    //echo '<option value="' . $project["Project"]["id"] . '">' . $project['Project']['Title'] . '</option>';
    $checked = '';
}

?>
<h4>Type</h4>
<input type="radio" name="type" value="data" checked> Answers<br/>
<input type="radio" name="type" value="questions"> Questions<br/>
<input type="radio" name="type" value="options"> Options<br/>
<input type="radio" name="type" value="scores" disabled="disabled"> Scores (not currently available)<br/>
<input type="radio" name="type" value="time_submitted" disabled="disabled"> Time Submitted (not currently available)<br/>

<h4>Patient Filter</h4>
<input type="checkbox" name="inclTestPatients" disabled="disabled"> Include test patients<br/>
<input type="checkbox" name="consentedEligibleOnly" checked disabled="disabled"> Consented and eligible participants only<br/>
<input type="checkbox" name="TxPatientsOnly" disabled="disabled"> Treatment participants only<br/>

<br/>
<input class="btn btn-large btn-primary" type="submit" id="getReport" value="Get Report"/>
</form>

</select>

<script type="text/javascript">

$(document).ready(function() { 
    
<?php 

$report = $this->Html->url('/data_access'); 
$allSessionTypesParam = '';
foreach($allSessionTypes as $type){
    $allSessionTypesParam .= $type . '.';
}

?>

    $("#getReport").click(function(){

        var projectId = $('input[name=project]:checked').val();
        var type = $('input[name=type]:checked').val(); // eg 'data'

        var reportUrl = "<?=$report;?>" 
            + "/" + type + "_export?" // eg /data_export?
            + "label=proj" + projectId + "." + type 
            + "&row_per_session=true" 
            + "&project=" + projectId 
            + "&type_array=<?=$allSessionTypesParam;?>";
//        alert('submit clicked, going to: ' + reportUrl);

        window.location.assign(reportUrl);

        //return false;
    });

});

</script>

<?php
/** 
**/
?>
    <h3>Legacy csv reports</h3>
    <ul>
    <? $report = $this->Html->url('/data_access'); ?>
    <li><a href="<?=$report;?>/data_export?label=AllSessions.tx&row_per_session=true&patient_filter=tx&type_array=APPT.NON_APPT.ELECTIVE.ODD_WEEK.EVEN_WEEK.EVEN_WEEK_8.EVEN_WEEK_12">
        Answers for all sessions (one row per session), for consented, eligible, non-test, treatment participants
    </a></li>
    <li><a href="<?=$report;?>/data_export">
        Answers for T sessions (consented, eligible, non-test participants)
    </a></li>
    <li><a href="<?=$report;?>/data_export?type_array=nonT&label=nonT.tx&row_per_session=true&patient_filter=tx">
        Answers for "nonT" (aka elective) sessions (one row per session) for consented, eligible, non-test, treatment participants
    </a></li>
    <li><a href="<?=$report;?>/scores_export?label=AllSessions.tx&row_per_session=true&patient_filter=tx">
        Scores for all sessions (one row per session), for consented, eligible, non-test, treatment participants
    </a></li>
    <li><a href="<?=$report;?>/scores_export?patient_filter=non_test&label=Ts">
        Scores for T1-T4 (all non-test patients)
    </a></li>
    <li><a href="<?=$report;?>/intervention_dose_export?label=intervention&demographics=true">
        Intervention Dose (consented, eligible, non-test, treatment participants)
    </a></li>
    <li><a href="<?=$report;?>/log_export?label=view_my_reports_visits&controller=results">
        "View My Reports Visits" (consented, eligible, non-test, treatment participants)
    </a></li>
    <li><a href="<?=$report;?>/log_export?label=teaching_tips_visits&controller=teaching">
        "Teaching Tips Visits" (consented, eligible, non-test, treatment participants)
    </a></li>
    <li><a href="<?=$report;?>/data_export?label=demog.participant&demographics=true&patient_filter=participant&question_filter=demographics&max_sessions=1">
        Demographics (consented, eligible, non-test participants)
    </a></li>
    <li><a href="<?=$report;?>/data_export?type_array=T1&label=demog.participant&demographics=true&patient_filter=participant&question_filter=ethnicity">
        Demographics (OLD T1 ORIENTED) (consented, eligible, non-test participants)
    </a></li>
    <li><a href="<?=$report;?>/data_export?type_array=T1&label=demog.consentIrrelevant&demographics=true&patient_filter=non_test&question_filter=demographics&max_sessions=1">
        Demographics (all non-test patients)
    </a></li>
    <li><a href="<?=$report;?>/audio_codings_export">
        Audio Codings Export (consented, eligible, non-test participants)
    </a></li>
    <li><a href="<?=$report;?>/chart_codings_export">
        Chart Codings Export (consented, eligible, non-test participants)
    </a></li>
    <li><a href="<?=$report;?>/options_export">
        Options
    </a></li>
    <li><a href="<?=$report;?>/questions_export">
        Questions
    </a></li>
    <li><a href="<?=$report;?>/time_submitted_export">
        Time Submitted
    </a></li>

    </ul>
<?php
/** 
*/
?>
    <h3>Key</h3>

    <h4>For all reports</h4>
    Note: all times are GMT<br/>
    <br/>
    -99 = No session<br/>
    -999 = No answers in session<br/>
    -9 = Skipped (question, item, or subscale)<br/>
    1 = Selected check<br/>
    0 = Unselected check<br/>

    <h3>Patient Demographics</h3>
    Hispanic: 1 = Yes; 2 = No; -9 = skipped
    <br>
    Race options: 1 = Yes; 0 = No; -9 = skipped
    <br>
    <br>

    <h3>Clinics</h3>

    <table class="table span4" id="calendar">
        <thead>
            <tr>
                <th>ID</th>
                <th>Clinic Name</th>
                <th>Site Name</th>
            </tr>  
        </thead>
        <tbody>
    <?php
    foreach($clinics as $clinic){
        echo "<tr>";
        echo "<td>" . $clinic['Clinic']['id'] . "</td>";
        echo "<td>" . $clinic['Clinic']['name'] . "</td>";
        echo "<td>" . $clinic['Site']['name'] . "</td>";
        echo "</tr>";
    }

    ?>
        </tbody>
    </table>

</div>
