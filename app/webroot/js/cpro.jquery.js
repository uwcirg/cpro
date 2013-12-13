/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

// functions used in survey to check whether input is numbers only and show
// help message/add red border - adapted from CNICS Malignancy
// TODO - Could be merged or used as an alternative to some of the built-in 
// validation (jquery.validate)?
function numberFormatMessage(objectid, message){
    $(".text-error").remove();
    $("#" + objectid).after("<span class='help-inline text-error'>"+ message +"</span>");
}
function validatenumberformat(objectid){
    var fieldnumber = $("#" + objectid).val();  
    if (fieldnumber =="" || fieldnumber==null || fieldnumber == -1) return true; //an empty string is considered valid
    r = isOnlyDigits(fieldnumber);
    if (r) {
        // ok - make sure border is default and remove error text if there.
        $("#"+objectid).css("border-color","#CCCCCC").parent().find('span.text-error').text('');
        return true;
    } else {
        // fail - add error message
        $("#"+objectid).css("border-color","Red");
        numberFormatMessage(objectid, "Enter numbers only, please");
        return false;
    }
}	
function isOnlyDigits(n) {
    var regex =/\D/g; 
    var r =  regex.test(n); // checking if there are any characters or not 
    return !r;
}
// end - numbers only functions
             
(function($) {
    $.extend({
        /* Removing this function - causes errors with jquery 1.8
         * Appears to be a remnant of extending an older version of jquery
         * see: http://stackoverflow.com/questions/122102/what-is-the-most-efficient-way-to-clone-a-javascript-object
         *clone: function(obj) {
            if(obj === null || typeof(obj) != 'object') {
                return obj;
            }
            var temp = new obj.constructor(); // changed (twice)
            for(var key in obj) {
                temp[key] = $.clone(obj[key]);
            }
            return temp;
        },*/
        filterNot: function(arr, f) {
            var res = [];
            for(var i=0; i<arr.length; i++) {
                if(!f(i, arr[i])) {
                    res[res.length] = arr[i];
                }
            }
            return res;
        },
        first: function(arr, f) {
            for(var i=0; i<arr.length; i++) {
                if (f(arr[i])) {
                    return [i, arr[i]];
                }
            }           
            return [arr.length, false];
        },
    
        last: function (arr, f) {
            for(var i=arr.length-1; i>=0; i--) {
                if(f(arr[i])) {
                    return [i, arr[i]];
                }
            }
            return [0, false];
        },
    
        sanitize: function(string) {
            return string.replace(/ |\/|\\/ig, "-");
        },

		// Change the CSS stylesheet in use on a page
		switchCSS: function(file) {
			var style = $("link[rel=stylesheet][title]");
			var currentFile = style.attr('href');
			var newFile = currentFile.replace(/[a-zA-Z\-]*\.css/, file);
			style.attr({href : newFile});
		},

        // intended to be called from survey controller
        ajaxAnswer:function(question, option, state, text, page_id, value) {
            url = "../answer/"+question+".json";
            if (iteration)
                url = '../'+url
            $.post(url,{
                "data[Answer][question_id]": question,
                "data[Answer][state]" : state,
                "data[Answer][body_text]" : text,
                "data[Answer][option_id]" : option,
                "data[Answer][value]" : value,
                "data[Answer][iteration]" : iteration,
                "data[Page][id]" : page_id,
                "data[AppController][AppController_id]" : acidValue
            });
        },

        // intended to be called from teaching controller
        ajaxRecordLinkToExternalResource:function(subscaleId, url) {

            //alert("ajaxRecordLinkToExternalResource(), here's window.location.pathname: " + window.location.pathname);
            // window.location.pathname like /sme-mcjustin/results/show/38

            //TODO urlencode the url!
            var urlClean = escape(url);
            //urlClean = "\"" + urlClean + "\"";
            urlClean = urlClean.replace(/\+/g, "%2B");
            urlClean = urlClean.replace(/\//g, "%2F");
            urlClean = urlClean.replace(/%/g, "PERCENTREPLACEME");
            //urlClean = "EXTERNAL_URL_TO_DECODE" + urlClean;
            //var params = subscaleId + "/" + escape(url);
            //var params = urlClean;
            //var params = urlClean.substring(0, 4);
            var params = subscaleId + "/" + urlClean;
            //alert("params: " + params);
			/**$.post("./teaching/log_click_to_external_resource/"+params,
			{ "data[AppController][AppController_id]" : acidValue});*/
			$.ajax(
			//{ url: "./teaching/log_click_to_external_resource/"+params });
			{ url: appRoot + controller + 
                    "/log_click_to_external_resource/" + params });
			/**$.post(
			    appRoot + controller + 
                    "/log_click_to_external_resource/" + params );*/
	    },
		
        // intended to be called from teaching controller
        ajaxRecordTeachingTipExpansion:function(subscaleId, text) {
            var params;
            if (!subscaleId){
                params = text;
            }
            else params = subscaleId + "/" + text;
			$.ajax(
			    { url: appRoot + controller + 
                "/log_teaching_tip_expansion/" + params });
			/**$.post(
			    appRoot + controller + 
                    "/log_teaching_tip_expansion/" + params );*/
	    },

        enableTips: function() {
            // Wrap each stem in a link and clicking it toggles response display
            $("div.stem").wrap("<a class='stem' href='#'></a>").append(' <i class="icon-blue"></i>');
            var checkCritical = $(".teaching-tips").attr('id');
            if (checkCritical == 'critical-value') {
                $("#critical-value div.stem i").addClass("icon-chevron-up");
            } else {
                $("div.stem i").addClass("icon-chevron-down");
            }
            $("div.stem").parent().click(function() {
                if( $(this).next("div.response").is(":visible") ) {
                    $(this).find("i").removeClass("icon-chevron-up").addClass("icon-chevron-down")
                    $(this).next("div.response").slideUp('fast');
                } else {
                    var subscale = $(this).attr('id');
                    var fieldsetsubscale = $(this).parents("fieldset.subscale").attr("id");
                    //var strArray = subscale.split('.');
                    //subscale = strArray[1];
                    //alert("ajaxRecordTeachingTipExpansion for subscale: " + subscale );
                    $.ajaxRecordTeachingTipExpansion(
                            fieldsetsubscale, 
                            //$(".stem").attr('text')
                            $(this).attr('text')
                            );
                    $(this).find("i").removeClass("icon-chevron-down").addClass("icon-chevron-up");
                    $(this).next("div.response").slideDown('fast').find("i");  
                }
                return false;
            });

            $("#show-all").click(function() {
                if(this.checked) {
                    $(".response").slideDown('fast');
                    $("i.icon-chevron-down").removeClass("icon-chevron-down").addClass("icon-chevron-up");
                    $.ajaxRecordTeachingTipExpansion(
                            null, 'Expand all teaching tips');
                } else {
                    $(".response").slideUp('fast');
                    $("i.icon-chevron-up").removeClass("icon-chevron-up").addClass("icon-chevron-down");
                }
            });

            // Hides tip links specific to other sites
            // Generic tips have not set class and thus are not hidden
            // First grab current site from hidden div
            var siteId = $("div.site-id").attr('id');
            // Find each tip link with a class, hide those where class is diff than current
            $('.tip-websites li[class]').each(function() {
                if (!$(this).hasClass("site-"+siteId)) {
                    $(this).addClass('hidden');
                }
            });

            // add pdf warnings for all links to .pdf files - new method employing CSS background by mark47
            // $("a[href$='.pdf']").append(" <img src='img/pdf.gif'/>");
            $("a[href$='.pdf']").addClass("pdf");
            // open resource links in a new window
            $(".response a, .fatigue-response a").attr('target', '_blank');

            // FIXME this is not ideal - would be better to iterate
            //      on the fieldsets from the top down
            //      but the 1.2 children fxn only returns immediate children
            //      and other approaches were problematic
            $(".response a, .fatigue-response a").attr('id', function (subscaleId){
                //linkCountForSubscale ++;
                return "subscale." + 
                        $(this).parents("fieldset.subscale").attr("id"); 
                        /**$(this).parents("fieldset .subscale").attr("id") 
                        + ".link." + linkCountForSubscale;*/
            });
            $(".response a, .fatigue-response a").click(function() {
                // id like "subscale.30.link.0"
                //FIXME currently, only subscale id recorded; link id is not
                var subscale = $(this).attr('id');
                var strArray = subscale.split('.');
                subscale = strArray[1];
                $.ajaxRecordLinkToExternalResource(
                            subscale, 
                            $(this).attr('href')
                            );
            });
        } // enableTips: function() {
    }); // $.extend({

    $.fn.extend({
        collapser: function() {
            $(this).each(function(i, item) {
                var col = $($(item).attr('href')).hide()
                $(this).click(function() {
                    col.toggle();
                    return false;
                });
            });
        },

        radioButtonLabels: function() {
            $(this).click(function() {
                //alert("click on radio button label");
                var button = "#" + $(this).attr('for');
                $(button).click();
                return false;
            });
        },

        checkboxButtons: function(settings) {
            $(this).on("change", function() {
                
                var findIcon = $(this).parent().find("i");
                if (this.checked) {
                    $(findIcon).addClass('icon-check').removeClass('icon-check-empty');
                } else {
                    $(findIcon).addClass('icon-check-empty').removeClass('icon-check');
                }

                var comboId = this.id + '-combo';
                var comboTxt = '';
                // if this checkbox is part of a combo-check
                // input fadesIn and Out on a separate row below the checkbox
                if ($('#' + comboId).length){
                    if (this.checked == false){
                        $('#' + comboId).parent().hide();
                        $('#' + comboId).val('');
                    } else {
                        $('#' + comboId).parent().show();
                        $('#' + comboId).focus();
                    }
                }
                else if (settings.lastQ == true) {
                    $("#next-arrow-link").focus();
                }
                $.ajaxAnswer(this.name,     // question 
                             this.value,    // option
                             this.checked, 
                             "", // text
                             settings.page_id,
                             comboTxt);
            });
        },

        selectButtons: function(settings) {
            $(this).on("change", function() {
                
                var selectVal, selectState;
                if ($(this).val() == "") {
                    selectVal = $(this).attr('data-prev');
                    selectState = false;
                } else {
                    selectVal = $(this).val();
                    selectState = true;
                }
                // If this is a date select, need to put value in different spot
                if ($(this).hasClass('date-select')) {
                    var id = this.id.split("-");
                    var question = id[0];
                    var option = id[1];
                    $.ajaxAnswer(this.name,     // question 
                             option,    // option
                             selectState, 
                             "", // text
                             settings.page_id,
                             selectVal); // actual value
                } else {
                    $.ajaxAnswer(this.name,     // question 
                             selectVal,    // option
                             selectState, 
                             "", // text
                             settings.page_id,
                             "");                    
                }
            });
        },
        
        // Add appropriate actions to radio buttons and the icons that are
        // shown for them.
        radioButtons: function(settings, selected) {
            settings = jQuery.extend({
                buttonSelector: ".radio-button",
                selected: selected,
                selectedRadioButtons: ".radio-button" + selected
                }, settings);

            // TODO: Use settings instead of hard-coded values
            $(this).click(function() {
                
                var id = this.id.split("-");
                var question = id[0];
                var option = id[1];
                var radioName = $(this).attr("name");
                
                // To allow unchecking of selected radios - needed to override
                // default radio behavior
                var previousValue = $(this).attr('data-prev');
                if (previousValue == 'checked') {
                    $(this).removeAttr('checked');
                    $(this).attr('data-prev', false);
                } else {
                    $("input[name=" + radioName + "]:radio").attr('data-prev', false);
                    $(this).attr('data-prev', 'checked');
                }
                // Change the icons that represent the radio input
                $(':radio[name="' + radioName + '"]').each(function () {
                    var findIcon = $(this).parent().find("i");
                    if (this.checked) {
                        $(findIcon).addClass('icon-circle').removeClass('icon-circle-blank');
                    } else {
                        $(findIcon).addClass('icon-circle-blank').removeClass('icon-circle');
                    }
                });
                
                // comboText-related functions
                var comboId = this.id + '-combo';
                if ($('#' + comboId).length){
                    // If there's a comboId then show and focus
                    $('#' + comboId).parent().fadeIn();
                    $('#' + comboId).focus();
                } else {
                    // Hide and clear comboText value if a different answer 
                    // is selected
                    // Need to specify ID that begins the question # and ends w/
                    // "combo" so that only the corresponding combo gets cleared
                    // Was an issue when multiple questions are on page.
                    $("input[id^="+question+"][id$=combo]").val("").parent('.comboTextHolder').hide();
                }
                var selectThis = !$(this).is('.selected');
                // Need to clear and hide if user deselects a radio button with
                // corresponding comboText
                if (!selectThis) {
                    $('#' + comboId).val("").parent().hide();
                }
                
                /*alert("click on radio button; here's id:" +
                    id + ", question:" + question + 
                    ", option:" + option + 
                    ", selectThis:" + selectThis);*/
                
                $(this).parents('.q-container')
                       .find(".radio-button.selected[id^='" + question + "']")
                       .removeClass('selected');
                if(selectThis) {
                    $(this).addClass('selected');
                }
                $.ajaxAnswer(question, option, selectThis, "", settings.page_id, '');

                // set focus on next button
                if (settings.lastQ == true) {
                    $("#next-arrow-link").focus();
                }
                
            });
        }, // radioButtons: function(settings, selected) {
       
        // non-combo text and textarea inputs are handled in views/surveys/show.ctp 
        comboTextInputs: function(settings) {
            $(this).keyup(function(){
                if ($(this).hasClass('numeric')) {
                    validatenumberformat($(this).attr('id'));
                }
                
                // this id like 1001-4255-combo
                //   ie question-option-combo
                strArray = this.id.split('-');               

                $.ajaxAnswer(strArray[0],     // question, eg 1001
                             strArray[1],    // option, eg 4255
                             true, //chkbx.checked, 
                             "", // text
                             settings.page_id,
                             $(this).val());
            }); 
        },

        imageUpload: function(settings) {
            $(this).on("change", function() {
                
                var imageToLoad = $(this).val();
                console.log(imageToLoad);
                $.ajaxAnswer(this.name,     // question 
                             this.value,    // option
                             "", // state?
                             "", // text
                             settings.page_id,
                             imageToLoad);
            });
        },

        // Label popups provide description of a label on hover
        popup: function(settings) {
            settings = jQuery.extend({
                label: ".label",
                message: ".message"
            }, settings);

            return $(this).each(function() {
                var label = $(settings.label, this);
                var message = $(settings.message, this);

                message.hide();

                $(this).hover(
                    function() {
                        message.show();
                    },
                    function() {
                        message.hide();
                    });

			});
        }, // popup: function(settings) {

        // Symptom coach popups put hidden text into a given div
        symptomCoach: function(settings) {
            settings = jQuery.extend({
                label: ".label",
                coaching: ".coaching",
                backToStart: "#backToStart",
                target: "div#coaching"
            }, settings);

            function show(coaching) {
                $(settings.coaching + ":visible", $(settings.target)).hide();
                coaching.show();
            }

            var target = $(settings.target);
            var coaching = target.find(settings.coaching);
            $(settings.backToStart).click(function() {
                show(coaching);
            });

            $(this).each(function() {
                var label = $(settings.label, this);
                var coaching = $(settings.coaching, this);

                target.append(coaching);
                coaching.hide();

                label.click(function() {
                    show(coaching);
                });
            });
        } // symptomCoach: function(settings) {
    }); // $.fn.extend({

})(jQuery);

