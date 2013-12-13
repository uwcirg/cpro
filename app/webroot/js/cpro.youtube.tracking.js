/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/   

/**
*event.data == YT.PlayerState.PLAYING, YT.PlayerState.PAUSED, YT.PlayerState.ENDED
*Details: https://developers.google.com/youtube/js_api_reference#Playback_status
*For tracking when users leaves page we pass our own action value - pageChange
*Action possible values:
*  loaded
*  play
*  pause
*  done
*  pageChange
*Param values (comma separated):
*  1st - videoName
*  2nd - timestamp in video
**/
function logVideo(event, unloadStatus) {
   var playerActionStatus;
   if (!unloadStatus) {
       if (event.data == 0){
           playerActionStatus = 'done'; 
       } else if (event.data == 1){
           playerActionStatus = 'play'; 
       } else if (event.data == 2){
           playerActionStatus = 'pause'; 
       } else {
           playerActionStatus = event.data;            
       }
   } else {
      playerActionStatus = event;
   }
   $.ajax ({
       type: "POST",
       url: appRoot + 'logs.json',
       dataType: 'json',
       async: true,
       data: {
           "data[AppController][AppController_id]" : acidValue,
           "data[Log][user_id]" : userId,
           "data[Log][controller]" : 'youtube',
           // Possible values are 0 - Ended, 1 - Play, 2 - Paused
           // Details: https://developers.google.com/youtube/js_api_reference#Playback_status
           // For tracking when users leaves page we pass our own action value
           "data[Log][action]" : playerActionStatus,
           // Params contains videoId and video time when action happens
           "data[Log][params]" : videoName + "," + player.getCurrentTime()
       },
       success: function () {
           //console.log('video logged');
       },
       error: function () {
           //console.log('video logging failed');
       }
   });
}
// videoCall allows capture of play/pause and other actions.
var player;
function videoCall(){
 player = new YT.Player('player', {
   events: {
     // Logs when video is done loading (on page load)
     'onReady': function (event) {
         logVideo("loaded", true);
     },
     // Logs standard youTube API calls - see above
     'onStateChange': function (event) {
         logVideo(event, null);
     }
   }
 });
}
$(window).on('beforeunload', function() {
   logVideo("pageChange", true);
});
   
