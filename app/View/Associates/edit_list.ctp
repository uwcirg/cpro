<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>

<div id="infopage">

<div  class="subsection">

    <h1>Settings</h1>
    <?php if ($this->Session->check('Message.flash')): echo $this->Session->flash(); endif; ?>  

    <div class="settings-group">
        <h2>Sharing Your Reports</h2>
        <div id="share-reports" class="settings-subgroup">
            <p>You are currently sharing reports with
            <?php
                $number = count($patntAssociates);
                echo $number . " " . ($number == 1 ? "associate" : "associates");
            ?> and with the clinic nurse. You can
            <a href="javascript:show_invite();">invite more people</a> 
            to share your reports with, or use the options below to change what 
            is currently being shared.
            </p>
            <?php foreach($patntAssociates as $patntAssociate) { ?>
                <form method="post" 
                    action="<?php echo $this->Html->url("/associates/edit/" .
                                $patntAssociate["PatientAssociate"]["id"]); ?>" 
                    class="<?php echo $patntAssociate["Associate"]["id"];?>">
                    <? echo $this->Form->hidden(AppController::CAKEID_KEY,
                       array('value' => $this->Session->read(AppController::ID_KEY)));                    ?>
                    <h4>
                    <?php print $patntAssociate["Associate"]["User"]["email"]; ?>
                    </h4>
                    <p>
                    <?php 
                    echo $this->Html->link('Remove this associate permanently',
                                    "/associates/delete/{$patntAssociate["PatientAssociate"]["id"]}?" .
                                        AppController::ID_KEY . "=" .
                                        $this->Session->read(AppController::ID_KEY),
                                    array(),
                                    "Are you sure you want to remove this associate? This action cannot be undone.");
                    ?>
<br/>
                    <a href="#" 
                        class="expand_associate"
                        >Edit what this associate can view</a>
                    </p>
                    <div class="expanded_associate">
                        <?php
    // build an array with the id's of subscales this associate can view
                            $selectedSubscales = array();
                            foreach(
                                $patntAssociate["Subscale"] as 
                                    $i => $subscale) {
                                array_push($selectedSubscales, $subscale["id"]);
                            }
    // render the scale/subscale share view with these selected
                            echo $this->element('share_subscales',
                                array("scales" => $scales,
                                "a_id" => $patntAssociate["Associate"]["id"],
                                "share_journal" => $patntAssociate["PatientAssociate"]["share_journal"],
                                "selectedSubscales" => $selectedSubscales));
                        ?>
                        <input type="submit" value="Save changes"/>
                    </div>
                </form>
<hr/>
            <?php } ?>
        </div>  
        <form action="<?php echo $this->Html->url("/associates/create"); ?>" 
                method="POST" id="create-associate" class="0"> 
            <? echo $this->Form->hidden(AppController::CAKEID_KEY,
                                  array('value' =>
                                        $this->Session->read(AppController::ID_KEY)));            ?>
            <div id="invite-to-share-0" class="share settings-subgroup">
                <h4>Invite someone to view your reports (step 1 of 4)</h4>
                <h4>Step 1: Email address</h4>
                <p>Please enter the email address for the person with whom you would like to share reports.
                </p>
		        <div class="input-container">
	                <label for="data[User][email]">Email 
	                </label>
	                <input id="data[User][email]" 
                            name="data[User][email]"
			    class="required"
                            />
		        </div>
		        <div class="input-container">
	                <label for="data[User][email_confirm]">Re-enter 
	                </label>
                	<input id='data[User][email_confirm]' name="data[User][email_confirm]"/>
		        </div>
                	<p style="clear:both;"><a href="#" id="0-next" class="next" onclick="if( 
$('#create-associate').validate().element('#data\\[User\\]\\[email_confirm\\]') && $('#create-associate').validate().element('#data\\[User\\]\\[email\\]')) { $('#invite-to-share-0').hide(); $('#invite-to-share-1').show(); } return false;">Next &raquo;</a></p>
            </div>

            <div id="invite-to-share-1" class="share settings-subgroup">
                <h4>Invite someone to view your reports (step 2 of 4)</h4>
                <h4>Step 2: Secret word</h4>
                <p>To view your reports, others will need to know a secret word you pick. Be sure to tell them this word in person or over the phone, as email is insecure.</p>
                <?php if($patient["Patient"]["secret_phrase"]) { ?>
                    <p>You can change your secret word in the box below. (This will change the secret word for all people you are sharing reports with. Be sure to inform them of the new word. Your secret word is case-sensitive.)</p>
                <?php } else { ?>
                    <p>You have not yet set a secret word. Please type one in the box below. (Your secret word is case sensitive.)</p>
                <?php } 
    # FIXME: what are the validation rules for the phrase? How strictly do we match it?
                ?>
                <input name="data[Patient][secret_phrase]" 
                    id="data[Patient][secret_phrase]" 
                    length="20" 
                    class="required"
                    minlength="3"
                    value="<?php 
                            echo $patient["Patient"]["secret_phrase"]; 
                            ?>"
                />
                <p>
                <a href="#" id="1-previous" onclick="$('#invite-to-share-1').hide(); $('#invite-to-share-0').show(); return false;">&laquo; Previous</a>
                    |  
                <a href="#" id="1-next" class="next" onclick="if( $('#create-associate').validate().element('#data\\[Patient\\]\\[secret_phrase\\]')) { $('#invite-to-share-1').hide(); $('#invite-to-share-2').show(); } return false;">Next &raquo;</a>
                </p>
            </div>

            <div id="invite-to-share-2" class="share settings-subgroup">
                <h4>Invite someone to view your reports (step 3 of 4)</h4>
                <h4>Step 3: Select reports to share</h4>
                <p>Select what reports you want to share.</p>
                <?php echo $this->element('share_subscales',
                                                array("scales" => $scales)); 
                ?>
                <p>
                <a href="#" id="2-previous" onclick="$('#invite-to-share-2').hide(); $('#invite-to-share-1').show(); return false;">&laquo; Previous</a>
                 | 
                <a href="#" id="2-next" class="next" onclick="$('#invite-to-share-2').hide(); $('#invite-to-share-3').show(); return false;">Next &raquo;</a>
            </div>

            <?php 
                $emailInviteBody = 
                    "You have been invited to view reports that " .
                    $patient["User"]["first_name"] . " " .
                    $patient["User"]["last_name"][0] . 
                    ". is entering online. You will need to learn the 'secret word' from " . $patient["User"]["first_name"] . " to unlock these reports. Then you can use the link below to register at your convenience. If you have already registered with ESRA-C, you just need " . $patient["User"]["first_name"] . "'s secret word.";
                $emailInviteSubject = 
                    "Invitation to View Reports"; 
            ?>
            <div id="invite-to-share-3" class="share settings-subgroup">
                <h4>Invite someone to view your reports (step 4 of 4)</h4>
                <h4>Step 4: Personalize the e-mail invitation</h4>
                <label for "data[Associate][invitationSubject]">Text for invitation subject line:<br/>
                    <textarea 
                        id="data[Associate][invitationSubject]" 
                        name="data[Associate][invitationSubject]" 
                        rows="1"
                        class="required"
                        minlength="3"
                        ><?php echo $emailInviteSubject; ?>
                    </textarea>
                </label>
                <br/>
                <label for "data[Associate][invitationBody]">Text for invitation (Note that a link to this website will be added automatically):
                    <textarea 
                        id="data[Associate][invitationBody]" 
                        name="data[Associate][invitationBody]" 
                        rows="10"
                        class="required"
                        minlength="3"
                        ><?php echo $emailInviteBody; ?>
                    </textarea>
                </label>
                <p>
                <a href="#" id="3-previous" onclick=" $('#invite-to-share-3').hide(); $('#invite-to-share-2').show(); return false;">&laquo; Previous</a>
                <input type="submit" value="Send invitation"/>
            </div>
        </form>
    </div>

</div>	

</div>

<script>  

$(function() {
    var info = $("div#settings-info");
    var groups = $('div.settings-group');
    var subgroups = $('div.settings-subgroup');
    groups.each(function() {
        var group = $(this);
        //group.show();
        /**group.find("h2").each(function() {
            var groupTitle = $(this);
            $("#quick-links").append(groupTitle);
        });*/
        group.find(".settings-subgroup").each(function() {
            var subgroup = $(this);
            var subgroupId = subgroup.attr('id');
            subgroup.find("h3").each(function () {
                var subgroupTitle = $(this);
                var subgroupTitleElement = 
                        $("<li></li>").text(subgroupTitle.html())
                            .attr({ 'class' : "some-class " + subgroupId});
                subgroupTitleElement.click(function() { 
                    //$("#quick-links li").removeClass("selected"); 
                    $(this).addClass("selected"); 
                    info.hide(); 
                    subgroups.hide();
                    subgroup.show(); 
                });
				//$("#quick-links").append(subgroupTitleElement);
			});
		});
	});

	subgroups.hide();

	$("a.switch-css").click(function() {
		$.switchCSS(this.id);
    });

    if(location.hash != "") {
        // FIXME: should also format this link as selected, but not yet a way to get it
        info.hide();
        subgroups.hide(); 
        $(location.hash).show();
    }

    $(".expand_associate").click(function() {
        $(this).parents("form").find(".expanded_associate").toggle();
	    $("div.message").hide(); // hide flash message
        return false;
    });

    $(".expanded_associate").hide();

    validatePatientForm("#create-associate");
    
    $(document).keypress(function (e){
        var code = (e.keyCode? e.keyCode : e.which);
        // trap back? code == 8
        if ((code == 13) || (code == 3)){
            $next = $("div:visible > p > .next");
            if($next.length) {
                $next.click();
                return false;
            } else {
                return true;
            }
        }
    });

    $("#share-reports").show();
    //$(#invite-to-share-0).show();
    //show_invite();
})

function show_invite() {
	$("div.settings-subgroup").hide(); // first hide all showing groups
	$("div.message").hide(); // hide flash message after first "page"
    $("#invite-to-share-0").show();
}

</script>
