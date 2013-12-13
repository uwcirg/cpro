<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<script>

$(document).ready(function(){
    
    $("#switchUser").click(function(){
        var newUser = $("select#user_selector option:selected").val();
        window.location.replace(
            "<?php echo Router::url("/logs/index/"); ?>" + newUser);
    });
  
    $("#allUsersLogs").click(function(){
        var newUser = $("select#user_selector option:selected").val();
        window.location.replace(
            "<?php echo Router::url("/logs/index"); ?>");
    });
  
<?php
    if ($log_user_id) {
        echo '$("select#user_selector").val("' . $log_user_id . '");';
    }
?>  
    
});

</script>

<?php
function paginationNav($paginator){
    echo "<span style=\"float:left\">";
    echo $this->Paginator->counter(array(
        'format' => 'Page %page% of %pages%, showing records %start% through %end% out of %count% total'
    )); 
    echo "</span>";
    echo "<span style=\"float:right\">";
    echo $this->Paginator->prev('Previous ', null, null, null) . " | ";
    echo $this->Paginator->numbers() . " | "; 
    echo $this->Paginator->next(' Next', null, null, null);
    echo "</span>";
}
?>

<div class="span2">
<?php echo $this->element('quick_links_admin_tab',
                            array("quick_links" =>
                                    $quick_links)); ?>
</div>

<div class="span10">
<?php
    if ($log_user_id){
        echo "<h2>Logged Actions For User $log_user_id</h2>";
    }
    else 
        echo "<h2>Logged Actions For All Users</h2>";

    if ($centralSupport == true){

		echo "<form action=\"\">";
    echo "<div>Select user: ";
    echo "<select style=\"margin: 0 5px\" id=\"user_selector\">";
    	  foreach($users as $user) {
    	    echo "<option value='" . $user["User"]["id"] . "'>" . 
                $user["User"]["id"] . ". " . $user["User"]["username"] . "</option>";
    	  }
      
    echo "</select>";
    echo "<input type=\"button\" class=\"btn\" value=\"Show logs for selected user\" id=\"switchUser\"/> &nbsp;&nbsp;-OR-&nbsp;&nbsp; <input type=\"button\" class=\"btn\" value=\"Show logs for all users\" id=\"allUsersLogs\"/>";
    echo "</div></form>";

    }

?>

<br clear="all" />
<h3>Results:</h3>

<!-- New (and improved) log table -->
<table class="table table-condensed table-bordered table-striped table-hover" id="log-table">
	<thead>
    <tr>
	  <?php
	  foreach($cols as $name => $field) {
	    //echo "<th id='$field' scope='col'>$name</th>";
	    echo "<th>" . $name . "</th>";
	  }
	  ?>
	</tr>
  </thead>
  <tbody>
	<?php foreach ($logs as $log): ?>
	<tr>
	  <?php 
	  foreach($cols as $name => $field) {
	    $value = $log['Log'][$field];
	    echo "<td class='$field'>$value</td>";
	  }
	  ?>
	</tr>
	<?php endforeach; ?>
  </tbody>
</table>

<br clear="all" /><br />


</div>