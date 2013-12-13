<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
	<div class="subsection left">
	  <h1>Create Staff Users</h1>
      <fieldset>
      <p>
        <?php
		    //echo 'User Aco aliases this user has create permissions on:<br/>';
		    echo 'You have permission to create users with the following roles (and to assign existing users to them as well):<br/>';
            echo "<ul>";
            foreach($creatableUserAcosAndMembers as 
                        $creatableUserAco => $members) {
                echo "<li>";
                echo str_replace("acoUsers", "", $creatableUserAco);
                //echo "$creatableUserAco includes the following users:<br/>";
                //echo "<ul>";
                //foreach($members as $user){
                //    echo "<li>" . $user['User']['username'] . "</li>";
                //}
                //echo "</ul>";
                echo "</li>";
            }
            echo "</ul>";
            echo "<br/>";

        ?>
      </p>
      </fieldset>
      <br><br>
	</div>
	
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
