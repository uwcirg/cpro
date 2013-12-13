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

    <h2>View non-admin users</h2>

    <?php
    if (empty($users)) {
    ?>
    <div class="well">
        <div>There are no non-admin users.</div>
    </div>
    <?php
    } else {
    ?>
    
<table class="table table-condensed table-bordered table-striped table-hover" id="view-nonadmin-users-table">
    <thead>
    <tr>
        <th>Username</th>
        <th>Role</th>
        <?php if ($canResetPassword) { echo '<th>&nbsp;</th>'; } ?>
    </tr>
  </thead>
  <tbody>
    <?php
    foreach($users as $user) {
        $acl_aliasListString = '';
        foreach($user['UserAclLeaf'] as $aro){
            $acl_aliasListString .= $aro['acl_alias'] . ' ';
        }
 
?>
    <tr>
        <td><?php echo $user['User']['username']; ?></td>
        <td><?= $acl_aliasListString; ?></td>
        <?php 
		    if ($canResetPassword) {
                echo '<td>' . $this->Html->link('Reset Password',
                         "/admin/resetPassword/{$user['User']['id']}?" .
                            AppController::ID_KEY . "=" .
                            $this->Session->read(AppController::ID_KEY)) . '</td>';
            }
        ?>
    </tr>
    <?php
    }
    ?>
</tbody>
</table>
<?php
}
?>

</div>