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
    <h2>Patient Search</h2>

    <ul>
        <li>Searches are case-insensitive. "ellen" will find "Ellen"</li>
        <li>All non-empty fields must match. For example, if you enter a first and last name, it will only find results that match both.</li>
        <li>Using a word fragment is OK. For example, "Ward" finds "Howard"</li>
    </ul>

    <br clear="all" />

    <table class="table table-bordered table-condensed" style="width: 70%" id="patient-search">
        <?php
        echo $this->Form->create();
        ?>
        <tr>
            <td> <?php echo $this->Form->input('User.first_name'); ?> </td>
            <td> <?php echo $this->Form->input('User.last_name'); ?> </td>
            <td> <?php echo $this->Form->input('Patient.MRN'); ?> </td>
        </tr>
        <tr>
            <td><?php echo $this->Form->input('User.username', array('required' => 'false')); ?></td>
            <td> <?php echo $this->Form->input('User.email', array('required' => 'false')); ?></td>
            <td> <?php echo $this->Form->input('Phone.phone'); ?> </td>
        </tr>
        <?php
        echo $this->Form->hidden(AppController::CAKEID_KEY, array(
            'value' => $this->Session->read(AppController::ID_KEY)
        ));
        ?>
        <tr>
            <td colspan="3" style="text-align: center; background-color: #ccc"> <?php echo $this->Form->submit("Search", array("class"=>"btn btn-large btn-primary")); ?> </td>
        </tr>
        <?php echo $this->Form->end(); ?>

    </table>

    <br clear="all" />

    <?php
    if (empty($patients)) {
        echo "<br/>No patients found";
    } else {
        ?>
        <div id="patientList">
            <table class="table table-striped table-bordered table-hover table-condensed patient-datatable" id="patient-search-results">
                <thead>
                    <tr>
                        <th>ID</th>                        
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Username</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($patients as $patient) {
                    ?>
                        <tr>
                            <td class="patient-id"><?php echo $patient['Patient']['id']; ?></td>
                            <td><?php echo $patient['User']['first_name']; ?></td>
                            <td><?php echo $patient['User']['last_name']; ?></td>
                            <td><?php echo $patient['User']['username']; ?></td>
                            <td><?php echo $patient['User']['email']; ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    ?>

</div>
