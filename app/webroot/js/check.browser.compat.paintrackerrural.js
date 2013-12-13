/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
    * requires browser.detect.js (http://www.quirksmode.org/js/detect.html)
*/


function checkDhairBrowserCompatibility() { 

    var compatible = "maybe";
    var os = BrowserDetect.OS;
    var browser = BrowserDetect.browser;
    var majorVersion = parseInt(BrowserDetect.version.toString().match(/([0-9]+)/)[0]);

    if (browser == 'Chrome'){
        compatible = "yes";
    }
    else if (browser == 'Safari'){
        if (majorVersion >= 3){
            compatible = "yes";
        } 
    }
    else if (browser == "Explorer"){
        if (majorVersion >= 7) {
            compatible = "yes";
        }
        else {
            compatible = "no";
        }  
    }
    else if (browser == 'Firefox'){
        if (os == 'Windows'){
            if (majorVersion >= 2) {
                compatible = "yes";
            } 
        }
        else if (os == 'Mac'){
            if (majorVersion >= 3) {
                compatible = "yes";
            } 
        }
    }

    return compatible; 
}
