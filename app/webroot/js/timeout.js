// Javascript to set timers to timeout after the correct amount of inactivity,
// where the correct amount is defined in the 'timeout' variable set elsewhere

// force a Logout.  prefix must be set elsewhere
function forceLogout() {
  //alert ("forceLogout()");
  window.location = prefix + "users/logout?timeout=true";
//   alert ("If this were working, you would have been timed out.");
}

// holds the timeout id
var timeoutId = 0;

// debugging variable to let you know when the timeout is reset
var count = 0;

// start the timeout
function startMyTimeout() {
  count++;
  timeoutId = setTimeout("forceLogout()", timeout);
// uncomment to get an idea of when the timeout is reset
//  $("div.top-links").append(count);
   //alert ("doing startMyTimeout w/ timeoutId = " + timeoutId);
  // update timeout when something happens, but only every so often; 
  // otherwise a lot of events get sent
  if (count == 500) {	
    count = 0;
    $.post(prefix + "/users/updateTimeout",
         {
             "data[AppController][AppController_id]" : acidValue
         });
  }

}

// restart the timeout
function resetMyTimeout() {
  //console.log("Resetting timeout");
  clearTimeout(timeoutId);
  startMyTimeout(); 
}

// restart timeout on any keypress and any click
$(function() {
   startMyTimeout();

   $("*").click(function() {
     resetMyTimeout();
   });
   $("*").keypress(function() {
     resetMyTimeout();
   });
   $(document).scroll(function(){
      resetMyTimeout();
   });
});
