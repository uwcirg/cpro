/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * Main file for storing jquery functions that are called throughout the
    * codebase.
    *
*/   

// AJAX to change language for currently logged in user or before login that 
// will then be saved to their record after login
function changeLanguageSelf(lang) {
    $.ajax ({
        type: "POST",
        url: appRoot + 'users/setLanguageForSelf',
        async: false,
        dataType: 'json',
        data: {"data[User][locale]" : lang , "data[AppController][AppController_id]" : acidValue}
    }).done(function(){
        window.location.reload();
    }).fail(function(){
        alert("There was a problem updating the language." + appRoot)
    });
}

// Show UW NetID login when "shift", "u" and "w" are typed at the same time
// Used for instances that have UWNETID_LOGIN as false. (if "true" it always is
// shown so this isn't need.
function typeForNetId() {
    var map = {16: false, 85: false, 87: false};
    $(document).keydown(function(e) {
        if (e.keyCode in map) {
            map[e.keyCode] = true;
            if (map[16] && map[85] && map[87]) {
                $("#uwLogin").fadeIn('slowUW');
            }
        }
    }).keyup(function(e) {
        if (e.keyCode in map) {
            map[e.keyCode] = false;
        }
    });
}
    
$(document).ready(function() {

    // Generic hide/show a section - used in patient edit. Specify the element(s)
    // to hide/show with the data-hide attribute on button
    $('.minimize-section').on("click", function(){
        var minLink = $(this);
        var toHide = $(minLink).attr('data-hide');
        $(toHide).toggle(0, function(){
            $(minLink).toggleClass('section-hidden');
            if($(toHide).is(":visible")) {
                $(minLink).html('<i class="icon-chevron-up"></i> Hide');
            } else {
               $(minLink).html('<i class="icon-chevron-down"></i> Show');
            }
        });
    })
    
    // Language switch - open confirmation modal
    $('#langSwitch').on('click', function() {
        var langChoice = $(this).attr('name');
        $("#langSwitchModal").modal();
        return false;
    });
    
    // Language switch function on click. After AJAX is passed, the page will
    // reload with the new language.
    $('#langSwitchConfirm').on('click', function() {
        var langChoice = $(this).attr('name');
        changeLanguageSelf(langChoice);
        return false;
    });

    // Enable Bootstrap popovers and add functionality
    $("[rel=popover]").popover({
        trigger: 'click',
        placement: 'top'
    });
    // Adds a close button to popover title when created
    function addPopoverClose() {
        $(".popover-title").append('<button type="button" id="closePopover" class="close">&times;</button>');
    }
    // Function to close any other popovers when clicking on a new one
    // Based on: http://stackoverflow.com/questions/12116725/executing-functions-before-bootstrap-popover-is-displayed
    var $visiblePopover;
    function popoverMgr($this) {
        // check if the one clicked is now shown
        if ($this.data('popover').tip().hasClass('in')) {
            // if another was showing, hide it
            $visiblePopover && $visiblePopover.popover('hide');
            // then store the current popover
            $visiblePopover = $this;
            addPopoverClose();
        } else { // if it was hidden, then nothing must be showing
            $visiblePopover = '';
        }
    }
    // Execute popoverMgr
    $('body').on('click', '[rel="popover"]', function() {
        var $this = $(this);
        popoverMgr($this);
        return false; // Prevents page moving up if href="#" is in <a> tag.
    });
    // Clicking on close button mimics a click on the original link
    $('body').on('click', '#closePopover', function() {
        $(this).closest("div").prev('[rel="popover"]').trigger('click');
    });
    
    // Enable Bootstrap tooltips
    $("[rel=tooltip]").tooltip();
    
    // Scroll to point on page based on href. This method allows an affixed 
    // column to move on clicks within page (uses Bootstrap affix)
    $(".scroll-on-page").on('click', function() {
        $('html,body').animate({scrollTop: $($(this).attr("href")).offset().top},{duration: 500, easing: "swing"});
        return false;
    });
    
    // Change date format to preferred dd/mm/yyyy
    $('input.datep').each(function(){
        
        var serverFormat = $(this).val();
        // if there's already a date and that date is in the YYYY-MM-DD (as 
        // found by having one or more dashes in serverFormat)
        if (serverFormat && (serverFormat.indexOf("-") != -1)) {
            if (serverFormat && serverFormat != '') {
                var serverFormatArray = serverFormat.split("-");
                var newFormat = serverFormatArray[1]+"/"+serverFormatArray[2]+"/"+serverFormatArray[0];
            }
            $(this).val(newFormat);
        }
    });
        
});

// Make height of left sidebar fill the entire window
function resizeSide() {
    var windowsize = $(window).width();
    if (windowsize > 767) {
        // If window is wider than 767 then calculature which is taller - the
        // overall window or the main container (span10). Then set the left
        // height based on that.
        var headerHeight = $(".esrac-header.row").height();
        var contOffset, mainHeight, contToChange;
        // container is named differently in survye layout vs. default
        if ($("body").hasClass('survey')) {
            contOffset = $(".survey-container").position().top;
            mainHeight = $(".survey-container").height();
            contToChange = "#surveySidebar";
        } else {
            contOffset = $(".intervention-container").position().top;
            mainHeight = $(".intervention-container").height();
            contToChange = ".intervention-container > .row > .span2";
        }
        if ($(window).height() > (mainHeight + headerHeight)) {
            //console.log("window is bigger ");
            var currentHeight = $(window).height() - contOffset;
        } else {
            //console.log("div is bigger ");
            var currentHeight = mainHeight;
        }
        $(contToChange).css("height", currentHeight + "px");
    } else {
        // If window is less than 767 wide, the nav is no longer on left -
        // everything is in the single column, so we don't want any extra height
        $(".intervention-container > .row > .span2").css("height", "");
        $("#surveySidebar").css("height", "");
    }
}
$(window).load(function(){
    // Fire resize when window finishes loading
    resizeSide();
    $(window).resize(function() {
        // Fire resize if browser window size changes. Delay by 200ms to avoid
        // too many firings as user drags browser window
        clearTimeout(this.id);
        this.id = setTimeout(resizeSide, 200);
    });
    //$(window).bind('resize', resizeSide);
});