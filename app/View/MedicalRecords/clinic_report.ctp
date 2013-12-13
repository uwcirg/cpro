<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <meta http-equiv="Content-Language" content="en-us" />

            <title>ESRAC Clinic Output</title>
            <style type="text/css" media="screen,print">

            <?php 
            echo ".page { 
                    padding-top: 20px;
                    min-height: 30px;
                    width: 6.5in;
                    height: 8in;
                    padding-top: 1.0in;
                    background: #fff url('" . Router::url("/", true) . "img/bannerprint.png') no-repeat top left;
                    page-break-before: always;
                    font: 80% Arial;
                    margin:0 auto;}";
            ?>

                .page .header {
                    border: 1px solid #000; padding: 10px; font-size: 1.3em; font-weight: bold
                }
                /*.footer {
                    border: 1px solid #000; border-width: 1px 0px 0px 0px;
                }*/

                #first-page.page {
                    page-break-before: avoid;
                }

                .column-left {
                    margin-right: 6%;
                }
                
                .column {
                    float: left;
                    width: 47%;
                }
                
                tr.new-symptom td { 
                    border-top: 1px solid #000;
                    padding-top: 3px;
                }
                td { 
                    /*padding:2px; */
                    padding: 2px 0px 2px 0px; 
                }

                td.barchart {
                    /*width: 50%;*/
                    width: 1.5in;
                    border: solid #000; 
                    border-width: 0px 1px 0px 1px;
                    margin: 10px 0px 10px 0px;
                }
                td.barchartkey {
                    width: .3in;
                    border: solid #000; 
                    border-width: 0px 0px 0px 1px;
                    /*margin: 10px 0px 10px 0px;*/
                    padding: 5px 0px 5px 0px;
                }
                td.indent-notice {
                    /*margin: 0px 0px 0px 10px;*/
                    padding: 0px 0px 0px 30px;
                    width: 2.20in;/*to avoid interfering w/ bar end in IE7*/
                }
                /*td.barchart.wide {
                    width: 50%;
                }*/

                table.for-charts {
                    width: 100%;
                }

                /* The following will only print properly in Windows
                    if the browser has been configured to print background
                    FF - Page Setup
                    IE - Internet Options, Advanced, Printing
                */
                div.bar { 
                    height: 10px; 
                    background-color: #FFF; 
                    /*border:1px solid #000;*/
                    border: solid #000;
                    border-width: 1px 1px 1px 0px;
                }
                div.bar.critical { 
                    background-color: #c66; 
                }
                /*
                The following may be a decent workaround if clinics
                don't want to mod their browser options.
                */
                /**
                div.bar { 
                    height: 0px; 
                    background-color: #FFF; 
                    border-left:0px;
                    border-right:0px;
                    border-top:6px solid #FFF;
                    border-bottom:xi65px solid #FFF;
                }
                div.bar.critical { 
                    background-color: #c66; 
                    border-top: 6px solid #c66;
                    border-bottom: 6px solid #c66;
                }
                */
                .alert_red {
                    color: #f00;
                }
                
                .float-left {
                    float: left;
                }
                .float-right {
                    float: right;
                }
                .italic {
                    font-style: italic;
                }
                .name {
                    /**width: 50%;*/
                }
                .dob {
                    /**width: 25%;*/
                    margin-left: 5%;
                }
                .pid {
                    /**width: 25%;*/
                    margin-left: 5%;
                }
                .not_reported {
                    /*font-style: italic;*/
                    font-size: smaller;
                    text-align: center;
                }
                div.keybox {
                    clear:both;
                    position:relative;
                    border: solid #000;
                    border-width: 1px 1px 1px 1px;
                    padding: 0px 0px 15px 75px; 
                }
                div.keyboxSpacer {
                    clear:both;
                    position:relative;
                    bottom:0;
                    height: 0.1in;
                }
                
            </style>
        </head>
        <body>
<?php
?>
            <div class="page" id="first-page">

<?php
    function printPatientInfoHeader($patient){
?>
                <div class="header">
                    <span class="name">
                    Name: <?echo $patient['User']['last_name'];?>, 
                    <?echo $patient['User']['first_name'];?> 
                    </span>
                    <span class="dob">
                    DOB: <?echo $patient['Patient']['birthdate'];?>
                    </span>
                    <span class="pid">
                    MRN: <?echo $patient['Patient']['MRN'];?>
                    </span>
                </div>
                <br/>
<?php
    } 

    function printTableRows($scaleId, $orderedSubscaleNamesAndIds, 
                            $sessions, $s_and_subs_index_by_id_w_scores,
                                    $numerical = false){
    foreach($orderedSubscaleNamesAndIds as $subscaleNameAndId){
        foreach($sessions as $session){
            $sessionApptId = $session['SurveySession']['appointment_id'];
            $boldThisSessionDate = false;
            if ($session == $sessions[sizeof($sessions) - 1]) {
                $boldThisSessionDate = true;
            }
?>
                                <tr
<?
            if ($sessionApptId == $sessions[0]['SurveySession']['appointment_id']){
                echo " class=\"new-symptom\"";
            }
            //else echo " class=\"\"";
?>
                                >
                                <td><!-- for date -->
<?
            if ($boldThisSessionDate){
                echo "<b>";
            }
            $date = $session['SurveySession']['lastAnswerDT'];
            if ($date){
                echo $date;
            }
            else echo "No report";
            if ($boldThisSessionDate){
                echo "</b>";
            }
?>
                                </td><!-- for date -->
<?php
            $incomplete = false;
            if (!is_null($s_and_subs_index_by_id_w_scores[$scaleId]
                        [$subscaleNameAndId['id']]
                        ["data_to_report_" . $sessionApptId])){
                
                echo "<td class=\"barchart\">";
                $scoreAsPercentage = 
                    $s_and_subs_index_by_id_w_scores[$scaleId]
                        [$subscaleNameAndId['id']]
                        ["data_to_report_" . $sessionApptId]
                        * 100;
                if (($numerical == true) && ($scoreAsPercentage == 0)){
                    echo "<b><i> 0</i></b>";
                }
                else{
                    //TODO FIXME create fxn to gen 0 mark; use for charts & key
                    if ($scoreAsPercentage == 0) $scoreAsPercentage = 1;
?>
                                <div class="bar
<?php 
                $critical = $s_and_subs_index_by_id_w_scores[$scaleId]
                                [$subscaleNameAndId['id']]
                                ["critical_" . $sessionApptId];
                
                if ($critical === true) echo ' critical';
?>
                                " style=
<?php 
                echo "\"width:" . $scoreAsPercentage . "%\"";
?>
                                >
                                </div>
<?php 
                }
?>                              
                                </td> <!-- if reported -->
<?php              
            }
            else {
                // if scale isn't PHQ9 (4) or PROMIS_FATIGUE_SCALE 
                if (($scaleId != PHQ9_SCALE) 
                    && ($scaleId != PROMIS_FATIGUE_SCALE)) 
                  echo " <td class=\"not_reported barchart\">Not reported</td>";
                else {
                    echo " <td></td>";
                    $incomplete = true;
                }
            }
?>                              
                                <!--/td--> <!-- needed? -->
<?php              
            // only add third column if a subscale name is to be printed
            if ($subscaleNameAndId['name'] != ''){
                // only write the name out once no matter how many sessions
                if ($sessions[0]['SurveySession']['appointment_id'] == $sessionApptId){
                    // make subscale name take 2 rows if # sessions > 1
                    if (sizeof($sessions) <= 1){
                        echo "<td> <!-- extra? -->";
                    }
                    else{
                        echo "<td rowspan=\"2\">";
                    }
                    echo $subscaleNameAndId['name'];
                    echo "</td><!--for optional third col name -->";
                }
            }
?>
                            </tr>
<?php 
            // The remainder are scale-specific special cases
            if($scaleId == PHQ9_SCALE){
                // the following would only be set if data_to_report_ is null
                if (array_key_exists("incomplete_but_high_" . $sessionApptId, 
                    $s_and_subs_index_by_id_w_scores[$scaleId]
                            [$subscaleNameAndId['id']])){
?>
            <tr><td class="indent-notice" colspan="2">Not reported, but scored high on depressed mood and/or anhedonia</td></tr>
<?php 
                }
                elseif ($incomplete === true){
?>
            <tr><td class="indent-notice" colspan="2">Not reported</td></tr>
<?php 
                }
                if (array_key_exists("red_alert_" . $sessionApptId, 
                    $s_and_subs_index_by_id_w_scores[$scaleId]
                            [$subscaleNameAndId['id']])){
?>
            <!-- problem here in IE 7, the alerts make the whole table wider
                    and the sclale end marker gets pushed to the right-->
            <tr class="alert_red"><td class="indent-notice" colspan="2">Alert!  Endorsed <i>"Thoughts that you would be better off dead or of hurting yourself in some way"</i>.</td></tr>
<?php 
                }
            }// if($scaleId == PHQ9_SCALE){

            if($scaleId == PROMIS_FATIGUE_SCALE){
                // the following would only be set if data_to_report_ is null
                if (array_key_exists("incomplete_but_high_" . $sessionApptId, 
                    $s_and_subs_index_by_id_w_scores[$scaleId]
                            [$subscaleNameAndId['id']])){
?>
            <tr><td class="indent-notice alert_red" colspan="2">Not reported, but one or more answers indicates serious impact of pain on daily activities</td></tr>
<?php 
                }
                elseif ($incomplete === true){
?>
            <tr><td class="indent-notice" colspan="2">Not reported</td></tr>
<?php 
                }
            }//if($scaleId == PROMIS_FATIGUE_SCALE){
        } // foreach($sessions as $session){
    }// foreach($orderedSubscaleNamesAndIds as $subscaleNameAndId){
}// function printTableRows
    
    printPatientInfoHeader($patient);


    // labels on this chart can differ from subscale.name
    $symptomsOrderedNamesAndIds = array(
      array('name' => 'Fatigue', 'id' => FATIGUE_SYMPTOM_SUBSCALE),
      array('name' => 'Nausea and Vomiting', 'id' => NAUSEA_VOMITING_SUBSCALE),
      array('name' => 'Pain', 'id' => PAIN_SUBSCALE),
      array('name' => 'Breathing', 'id' => BREATHING_SYMPTOM_SUBSCALE),
      array('name' => 'Sleeping Troubles', 'id' => SLEEPING_SUBSCALE),
      array('name' => 'Appetite Loss', 'id' => APPETITE_SYMPTOM_SUBSCALE),
      array('name' => 'Constipation', 'id' => CONSTIPATION_SUBSCALE),
      array('name' => 'Diarrhea', 'id' => DIARRHEA_SUBSCALE),
      array('name' => 'Impact on Sexuality', 'id' => SEXUALITY_SUBSCALE));
?>        
                <div class="column column-left">
                    <h2>QLQ C-30</h2>
                    <h3>(Symptoms Scales)</h3>
                    <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th><span class="float-left italic">Better</span>
                                    <span class="float-right italic">Worse</span>
                                </th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(SYMPTOMS_SCALE, $symptomsOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                        </tbody>
                    </table><!-- END OF SDS TABLE-->

                <br/>
                <br/>

                <h2>Patient's Top 2 Problems</h2>
                    <p class="italic">"The two problems bothering you most right now"</p>
                    <dl>
<?php
    foreach ($sessions as $session){
       // show session time 
?>
                        <dt>
<?php
        if ($session == $sessions[sizeof($sessions) - 1]) echo "<b>";
        echo $session['SurveySession']['lastAnswerDT'];
        if ($session == $sessions[sizeof($sessions) - 1]) echo "</b>";
?>
                        </dt>
                        <dd>
<?php
        if (array_key_exists(
                'priority_subscales', $session['SurveySession'])){
            if (array_key_exists(
                    0, $session['SurveySession']['priority_subscales'])){
                echo $session['SurveySession']['priority_subscales'][0];
                if (array_key_exists(
                    1, $session['SurveySession']['priority_subscales'])){
                    echo "<br/>" . 
                        $session['SurveySession']['priority_subscales'][1];
                }
                else{
                    echo "<br/>(Patient only prioritized one problem)";
                }
            }
            else{
                echo "(Patient did not prioritize problems)";
            }
        }
        else{
            echo "(Patient did not prioritize problems yet)";
        }
?>
                        </dd>
<?php
    }
?>
                    </dl><!-- END OF TOP 2 PRIORITIES SECTION-->

                </div>
                <div class="column">
<?php
    // labels on this chart can differ from subscale.name
    $finsOrderedNamesAndIds = array(
        array('name' => '', 'id' => FINS_SUBSCALE));
?>
                    <h2>Fatigue Numerical Intensity Scale</h2>
                    <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th><span class="float-left italic">0</span>
                                    <span class="float-right italic">10</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(FINS_SCALE, $finsOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores, true);
?>
                        </tbody>
                    </table><!-- END OF PINS TABLE-->

                <br/>
                <br/>

<?php
    // labels on this chart can differ from subscale.name
    $promisOrderedNamesAndIds = array(
        array('name' => '', 'id' => PROMIS_FATIGUE_SUBSCALE));
?>        
                    <h2>PROMIS Fatigue</h2>
                    <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th><span class="float-left italic">Low</span>
                                    <span class="float-right italic">High</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(PROMIS_FATIGUE_SCALE, $promisOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores, true);
?>
                        </tbody>
                    </table><!-- END OF PROMIS_FATIGUE_SCALE TABLE-->

                <br/>
                <br/>

<?php
    // labels on this chart can differ from subscale.name
    $pinsOrderedNamesAndIds = array(
        array('name' => '', 'id' => PINS_SUBSCALE));
?>
                    <h2>Pain or Discomfort</h2>
                    <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th><span class="float-left italic">0</span>
                                    <span class="float-right italic">10</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(PINS_SCALE, $pinsOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores, true);
?>
                        </tbody>
                    </table><!-- END OF PINS TABLE-->

                <br/>
                <br/>

<?php
    // labels on this chart can differ from subscale.name
    $phq9OrderedNamesAndIds = array(
        array('name' => '', 'id' => PHQ9_SUBSCALE));
?>        
                    <h2>Depression (PHQ-9 Inventory)</h2>
                      <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th>
                                    <span class="float-left italic">Mild</span>
                                    <span class="float-right italic">Severe</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(PHQ9_SCALE, $phq9OrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                        </tbody>
                    </table><!-- END OF PHQ9 TABLE-->
                </div>
            </div>

            <div class="page">
            <!--div class="nonkeyarea"-->
<?php
    printPatientInfoHeader($patient);
?>

                <div class="column column-left">
                    <h2>Quality of Life</h2>
                    <h3>(QLQ C-30 Scales)</h3>
<?php
    // labels on this chart can differ from subscale.name
    $qolFxnalOrderedNamesAndIds = array(
        array('name' => 'Physical', 'id' => QOL_PHYSICAL_SUBSCALE),
        array('name' => 'Emotional', 'id' => QOL_EMOTIONAL_SUBSCALE),
        array('name' => 'Social &amp; Family', 'id' => QOL_SOCIAL_FAM_SUBSCALE),
        array('name' => 'Cognitive', 'id' => QOL_COGNITIVE_SUBSCALE),
        array('name' => 'Work &amp; Leisure', 'id' => 
                                                    QOL_WORK_LEISURE_SUBSCALE),
        array('name' => 'Financial Difficulties', 'id' => 
                                                    QOL_FINANCIAL_SUBSCALE));
    
    $qolNeuroOrderedNamesAndIds = array(
        array('name' => 'Sensory', 'id' => SENSORY_SUBSCALE),
        array('name' => 'Motor', 'id' => MOTOR_SUBSCALE),
        array('name' => 'Autonomic', 'id' => AUTO_SUBSCALE));
    
    $qolOverallOrderedNamesAndIds = array(
        array('name' => 'QOL/Health', 'id' => QOL_OVERALL_SUBSCALE));
?>        
                    <table class="for-charts">
                        <tbody>
                            <tr><th colspan="3">Functional Subscales</th></tr>
                            <tr>
                              <th></th>
                              <th><span class="float-left italic">Better</span>
                                  <span class="float-right italic">Worse</span>
                              </th>
                              <th></th>
                            </tr>
<?php 
    printTableRows(QOL_SCALE, $qolFxnalOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr><th colspan="3">Peripheral Neuropathy Subscales</th></tr>
                            <tr>
                              <th></th>
                              <th><span class="float-left italic">Better</span>
                                  <span class="float-right italic">Worse</span>
                              </th>
                              <th></th>
                            </tr>
<?php 
    printTableRows(NEURO_SCALE, $qolNeuroOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                            <tr><td></td></tr>
                            <tr><td></td></tr>
                            <tr><th colspan="3">Overall</th></tr>
                            <tr>
                              <th></th>
                              <th><span class="float-left italic">Better</span>
                                  <span class="float-right italic">Worse</span>
                              </th>
                              <th></th>
                            </tr>
<?php 
    printTableRows(QOL_SCALE, $qolOverallOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                        </tbody>
                    </table><!-- END OF QOL TABLE-->
                    
    <!--table>
                        <thead>
                            <tr>
                                <th><h2>Key</h2></th>
                            </tr>
                        </thead>
                        <tbody>
    <tr>
        <td class="barchart">
            <div class="bar" style="width:1%">
            </div>
        </td>
        <td>Indicates least symptom distress, best quality of life, <br/>lowest impact, or lowest symptom score.
        </td>
    </tr>
    <tr>
        <td class="barchart">
            <div class="bar critical" style="width:75%"></div>
        </td><td>Shading indicates problems.</td>
    </tr>
    </tbody>
    </table-->
                </div><!--class="column column-left"-->

                <div class="column">
<?php
    // labels on this chart can differ from subscale.name
    $skinOrderedNamesAndIds = array(
        array('name' => '', 'id' => SKIN_SUBSCALE));
?>        
                    <h2>Skin Problem Severity</h2>
                    <table class="for-charts">
                        <thead>
                            <tr>
                                <th></th>
                                <th><span class="float-left italic">Low</span>
                                    <span class="float-right italic">High</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
<?php
    printTableRows(SKIN_SCALE, $skinOrderedNamesAndIds, 
                    $sessions, $sAndSubsIndexByIdWScores);
?>
                        </tbody>
                    </table><!-- END OF SKIN TABLE-->

                <br/>
                <!--br/-->

 
                    <h2>Open Response</h2>
                    <p class="italic">"Anything else you would like to discuss, or any questions you have for your care team."</p>
                    <dl>
<?php
    foreach ($sessions as $session){
       // show session time 
?>
                        <dt>
<?php
        if ($session == $sessions[sizeof($sessions) - 1]) echo "<b>";
        echo $session['SurveySession']['lastAnswerDT'];
        if ($session == $sessions[sizeof($sessions) - 1]) echo "</b>";
?>
                        </dt>
                        <dd>
<?php
        if (array_key_exists(
                'open_text', $session['SurveySession'])){
            echo $session['SurveySession']['open_text'];
        }
        else{
            echo "(Patient did not type text)";
        }
?>
                        </dd>
<?php
    }
?>
                    </dl><!-- END OF TOP 2 PRIORITIES SECTION-->

                </div> <!-- column-->
            <!--/div--> <!-- nonkeyarea -->
<div class="keyboxSpacer">
</div>
<div class="keybox">
    <table>
                        <thead>
                            <tr>
                                <th><h2>Key</h2></th>
                            </tr>
                        </thead>
                        <tbody>
    <tr>
        <td class="barchart">
            <div class="bar" style="width:1%">
            </div>
        </td>
        <td>No problem</td>
    </tr>
    <tr>
        <td class="barchart">
            <div class="bar" style="width:50%"></div>
        </td><td>Minimal problem</td>
    </tr>
    <tr>
        <td class="barchart">
            <div class="bar critical" style="width:75%"></div>
        </td><td>Moderate to severe problems</td>
    </tr>
    </tbody>
    </table>
</div>
            </div> <!-- page -->
        </body>
    </html>
