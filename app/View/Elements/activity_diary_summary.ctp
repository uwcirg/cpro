<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

// looks for startdate. If not there, displays most recent 7 days (including today).
if ( isset($startdate) ) {
    if ( $startdate > date('Y-m-d', strtotime("-7 days")) ) {
        $oneweek = date('Y-m-d', strtotime("-6 days"));
        $endofweek = date('Y-m-d');
    } else {
        $oneweek = date("Y-m-d", strtotime($startdate));
        $endofweek = date("Y-m-d", strtotime($startdate . "+6 days"));
    }
} else {
    $oneweek = date('Y-m-d',strtotime("-6 days"));
    $endofweek = date('Y-m-d');
}
$resultsheader = 'Entries for the week of ' . date("M. j",strtotime($oneweek)) . ' - ' . date("M. j",strtotime($endofweek));

// Gets array keys used to display entries below                                    
if ( $entries ) {
    $keys = array_keys($entries[0]["ActivityDiaryEntry"]);
}

// Counts entries in array (to avoid offset errors farther down )
$arraycount = count($entries);
$arraycount = $arraycount - 1;

?>
<h3>Activity Diary Summary</h3>

    
    <?php
    if($consent_status == 'consented') {
        echo '<p>';
        echo $this->Html->link('View Activity Diary details', '/patients/activityDiary/' . $patientId);
        echo '</p>';
        echo '<div><strong>' . $resultsheader . '</strong></div>';
        echo '<ul>';
        echo '<li>Number of days with entries added: ';
        echo $arraycount+1;
        echo '</li>';
        if ( $arraycount > 0 ) {
            $total = 0;
            $denom = 0;
            foreach($entries as $entry)
            {
                if ( $entry['ActivityDiaryEntry']['fatigue'] != '' ) {
                    $denom += 1;
                }
                $total += $entry['ActivityDiaryEntry']['fatigue'];
            }
            if ( $denom > 0 ) {
                echo '<li>Average fatigue level this week: ';
                echo round($total / $denom,1);
                echo '</li>';
            }
            $total = 0;
            $denom = 0;
            foreach($entries as $entry)
            {
                if ( $entry['ActivityDiaryEntry']['minutes'] != '' ) {
                    $denom += 1;
                }
                $total += $entry['ActivityDiaryEntry']['minutes'];
            }
            if ( $denom > 0 ) {
                echo '<li>Average minutes exercised per day this week: ';            
                echo number_format($total / $denom);
                echo '</li>';
            }
            $total = 0;
            $denom = 0;
            foreach($entries as $entry)
            {
                if ( $entry['ActivityDiaryEntry']['steps'] != '' ) {
                    $denom += 1;
                }
                $total += $entry['ActivityDiaryEntry']['steps'];
            }
            if ( $denom > 0 ) {
                echo '<li>Average steps per day this week: ';
                echo number_format($total / $denom);
                echo '</li>';
            }
        }
        ?> 
        </ul>

        <p><strong>All entries</strong><br />
        Statistics for all diary entries by this patient:</p>

        <ul>
        <?php
        echo '<li>Total number of days with entries: ' . count($allEntries) . '</li>';
        $total = 0;
        $denom = 0;
        foreach($allEntries as $allEntry)
        {
            if ( $allEntry['ActivityDiaryEntry']['fatigue'] != '' ) {
                $denom += 1;
            }
            $total += $allEntry['ActivityDiaryEntry']['fatigue'];
        }
        if ( $denom > 0 ) {
            echo '<li style="margin-top: 8px">Average fatigue level: ';
            echo round($total / $denom,1);
            echo '</li>';
        }
        $total = 0;
        $denom = 0;
        foreach($allEntries as $allEntry)
        {
            if ( $allEntry['ActivityDiaryEntry']['minutes'] != '' ) {
                $denom += 1;
            }
            $total += $allEntry['ActivityDiaryEntry']['minutes'];
        }
        if ( $denom > 0 ) {
            echo '<li style="margin-top: 8px">Average minutes exercised per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
        echo '<li>Total exercise time: ';
        echo number_format($total);
        echo ' minutes';
        if ( $total > 180 ) {
            echo ' - that\'s more than ';
            echo floor($total / 60);
            echo ' hours!';
        }
        echo '</li>';
        $total = 0;
        $denom = 0;
        foreach($allEntries as $allEntry)
        {
            if ( $allEntry['ActivityDiaryEntry']['steps'] != '' ) {
                $denom += 1;
            }
            $total += $allEntry['ActivityDiaryEntry']['steps'];
        }
        if ( $denom > 0 ) {
            echo '<li style="margin-top: 8px">Average steps per day: ';
            echo number_format($total / $denom);
            echo '</li>';
        }
        echo '<li>Total number of steps taken: ';
        echo number_format($total);
        if ( $total > 10000 ) {
            echo ' - that\'s approximately ';
            echo round($total / 2000,1);
            echo ' miles!';
        }
        echo '</li>';

    } else { // End if consented
        echo '<p>No activity diary entries.</p>';
    }
?>
</ul>


