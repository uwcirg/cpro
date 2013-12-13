<?php
/**
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
*/

if ($nMonths == 1) $nMonths = 'one';
elseif ($nMonths == 6) $nMonths = 'six';

?>

<div class="span2">
<?php
echo $this->element(
    'quick_links_admin_tab',
    array('quick_links' => $quick_links)
);
?>
</div>

<div class="span10">

<h2><?=Inflector::camelize($nMonths);?> Month Follow-up Eligible Patients</h2>

<table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="<?=$nMonths;?>-month-table">
    <thead>
<?php
echo $this->Html->tableHeaders(array(
    'Patient ID',
    'First Name',
    'Last Name',
    // '1-Month FU Pref.',
    'Start',
    'Stop',
    'Preference'
));
?>
    </thead>
    <tbody>
<?php        
foreach($reportablePatients as $patient){
    echo '<tr><td class="patient-id">';
    echo $patient['Patient']['id']."</td>";
    echo "<td>".$patient['User']['first_name']."</td>";
    echo "<td>".$patient['User']['last_name']."</td>";
    echo "<td>".$patient['PatientExtension']['start']->format('m/j/Y')."</td>";
    echo "<td>".$patient['PatientExtension']['stop']->format('m/j/Y')."</td>";
    echo "<td>";
    echo ($patient['Patient'][$nMonths . '_month_mode_pref']) ? $patient['Patient'][$nMonths . '_month_mode_pref'] : "";
    echo "</td>";
    // Lastname, firstname
//    echo String::insert(
//        sprintf(
//            '<td>%s</td>',
//            $this->Html->link(
//                ':lastname, :firstname',
//                array(
//                    'controller' => 'patients',
//                    'action' => 'edit',
//                    $patient['Patient']['id']
//                )
//            )
//        ),
//        array(
//            'firstname' => $patient['User']['first_name'][0].'.',
//            'lastname' => $patient['User']['last_name']
//        )
//    );

    // 1-month follow-up assessment start date
//    printf(
//        '<td>%s</td>',
//        $this->Html->link(
//            $patient['PatientExtension']['start']->format('m/j/Y'),
//            array(
//                'controller' => 'patients',
//                'action' => 'edit',
//                $patient['Patient']['id']
//            )
//        )
//    );

    // 1-month follow-up assessment stop date
//    printf(
//        '<td>%s</td>',
//        $this->Html->link(
//            $patient['PatientExtension']['stop']->format('m/j/Y'),
//            array(
//                'controller' => 'patients',
//                'action' => 'edit',
//                $patient['Patient']['id']
//            )
//        )
//    );
    echo '</tr>';
}
?>
    </tbody>
</table>
