$(document).ready(function() {

    // Add previously saved answers
    var paths = document.getElementsByTagName('path');
    for (var i=0; i<paths.length; i++)
        if(answers[paths[i].id])
            paths[i].className.baseVal += " " + answers[paths[i].id].join(" ");

    // Add click listeners to body parts
    $('path').click(keyClicked);

});

$.ajaxSetup({
    type: "POST",
    dataType: 'json',
    global: true
});

function keyClicked(e) {
    var node = e.target;
    var question_id = null;
    var answerState = null;

    // JSON data to post
    var data = {"data":{
        "Page":{"id":page_id},
        "Answer":{
            "question_id": PAIN_QUESTION_ID,
            // "option_id": parseInt(node.id),
            "body_text": node.className.baseVal,
            "state": false,
            "iteration": iteration,
        },
        "AppController" : {"AppController_id": parseInt(acidValue)}
    }};

    // This may be simplified by using this solution:
    // http://stackoverflow.com/a/15520393/796654
    // Remove PAIN_CLASS, add SEVERE_PAIN_CLASS
    if (node.classList.contains(PAIN_CLASS)){

        // Set option to correspond to pain question
        data["data"]["Answer"]["option_id"] = bodypart_map[node.id][PAIN_QUESTION_ID];

        // Send POST to toggle last state (PAIN_CLASS) to off
        var request = $.ajax({
            url: [
                appRoot,
                "surveys/answer/",
                PAIN_QUESTION_ID
            ].join(''),
            data: data
        });

        // Remove SEVERE_PAIN_CLASS from all other nodes
        // severe pain can only be indicated in one location
        $('path.'+SEVERE_PAIN_CLASS).each(function(i, obj) {
            this.classList.remove(SEVERE_PAIN_CLASS);
            this.classList.add(PAIN_CLASS);

            // Send POST re-enabling pain areas
            // This may not be necessary if analysis is done on raw data
            data["data"]["Answer"]["state"] = true;
            data["data"]["Answer"]["option_id"] = bodypart_map[this.id][PAIN_QUESTION_ID]
            var request = $.ajax({
                url: [
                    appRoot,
                    "surveys/answer/",
                    PAIN_QUESTION_ID
                ].join(''),
                data: data
            });

        });

        question_id = SEVERE_PAIN_QUESTION_ID;
        node.classList.remove(PAIN_CLASS);
        answerState = node.classList.toggle(SEVERE_PAIN_CLASS);
    }

    // Remove SEVERE_PAIN_CLASS, reset node
    else if (node.classList.contains(SEVERE_PAIN_CLASS)){
        question_id = SEVERE_PAIN_QUESTION_ID;
        answerState = node.classList.toggle(SEVERE_PAIN_CLASS);
    }

    // Add PAIN_CLASS
    else {
        question_id = PAIN_QUESTION_ID;
        answerState = node.classList.toggle(PAIN_CLASS);
    }

    data["data"]["Answer"]["state"] = answerState;
    data["data"]["Answer"]["question_id"] = question_id;
    data["data"]["Answer"]["body_text"] = node.className.baseVal;
    data["data"]["Answer"]["option_id"] = bodypart_map[node.id][question_id];

    // Set defaults with $.ajaxSetup
    // http://api.jquery.com/jQuery.ajaxSetup/
    var request = $.ajax({
        url: [
            appRoot,
            "surveys/answer/",
            question_id
        ].join(''),
        // async: false,
        data: data
    });
}
