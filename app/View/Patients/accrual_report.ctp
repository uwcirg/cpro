<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
</div>

<div class="span10">
    
    <h2>Accrual report</h2>
    <div>
    <b>IRB approval to enroll:</b><br/>707 / 87 / 795 patients  (175 / 75 / 250 clinicians)<br/>
    <b>Key:</b><br/>
    DFCI/SCCA/Total<br/>
    T1s (UC) + T1s (Consent) = Total T1s<br/>
    </div>
    <br/>
    
    <div>
    <?php $studyGroups = '(' . Patient::TREATMENT . '/' . Patient::CONTROL . ')'; ?>
    <b>
    <?php 
        echo Patient::HOME . " $studyGroups : ";
    ?>
    </b>
    <?php
        echo $homeCounts[Patient::TREATMENT] . '/' . $homeCounts[Patient::CONTROL];
    ?>
    &nbsp;
    <b>
    <?php 
        echo Patient::CLINIC . " $studyGroups : ";
    ?>
    </b>
    <?php
        echo $clinicCounts[Patient::TREATMENT] . '/' . 
             $clinicCounts[Patient::CONTROL];
    ?>
    </div>
    <br />
    <table class="table table-condensed table-bordered table-striped table-small-text" id="accrual">
        <tr>
            <th>Month</th>
            <th>T1s (UC)</th>
            <th>T1s (Consent)</th>
            <th>T1s (Target)</th>
            <th>Consent Rate</th>
            <th>T2s (UC)</th>
            <th>T2s (Consent)</th>
            <th>T2s (Target)</th>
            <th>
            <?php
                echo $this->Html->link('T2 Audio', '/audio_files/viewAll');
            ?>
            </th>
            <th>T3s</th>
            <th>T4s</th>
            <th>
            <?php
                echo $this->Html->link('Off-study', 'offStudy');
            ?>
            </th>
        </tr>
    <?php

    if (empty($rows)) {
    ?>
        <tr>
            <td colspan=12>No data</td>
        </tr>
    <?php
    } else {
    //$this->log(print_r($rows, true));
        foreach($rows as $row) { 
    ?>
        <tr>
            <th><?php echo $row['name']; ?></th>
            <?php
                foreach ($columns as $column) {
                    if (!$column['display']) {   // don't display this column
                        continue;
                    }

                    $columnName = $column['name'];
                    echo '<td>';

                    if ($column['sites']) {
                        foreach ($sites as $site) {
                            $siteName = $site['Site']['name'];
                            echo $row[$columnName][$siteName] . ' / ';
                        }
                    }

                    if (!isset($row[$columnName]['total'])) {
                        // total must be percentage
                        echo "{$row[$columnName]['totalPercent']}%";
                    } else {
                        echo $row[$columnName]['total'];

                        if (isset($row[$columnName]['totalPercent'])) {
                            echo " ({$row[$columnName]['totalPercent']}%)";
                        }
                    }

                    echo "</td>\n";
                }
            ?>
        </tr>
    <?php
        }
    }
    ?>
    </table>
    <br />
    <p><?php echo $this->Html->link('<i class="icon-download-alt"></i> Export as CSV', 'accrualReport/csv/true', array('class' => 'btn btn-small', 'escape' =>  false)); ?></p>

</div>