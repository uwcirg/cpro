<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

?>

<script>
// THIS IS TO PREVENT MULTIPLE COMPLETE REQUESTS
    var calculatingSession = false;

    $(function(){

        $(".calcInit").click(function() {
            var toLoad = $(this).attr('href');
            if (calculatingSession === true) {
                return false;
            }
            calculatingSession = true;
            $("#calculating-box").modal({
                backdrop: 'static',
                keyboard: false
            });
            setTimeout(function() {
                window.location = toLoad
            }, 4000);
            return false;
        });


<?php
        if ($this->Session->read('calculatingSession' . $session_id)){
?>
            calculatingSession = true;
            $("#calculating-box" ).modal();
            // reload to check calculatingSession again
            setTimeout(function(){location.reload();}, 10000);
<?php
        }
?>

    }); // onload 
</script>


<div id="calculating-box"  class="modal hide fade">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3><?=__('Calculating Your Answers')?></h3>
  </div>
  <div class="modal-body">
    <p><?=__("One moment please while the system saves your answers.")?></p>
    <p><?php echo $this->Html->image('loading.gif', array('alt'=>'Saving', 'style'=>'vertical-align: middle')); ?></p>
  </div>
</div>




