
    if(checkDhairBrowserCompatibility() == "no"){
        var msg = "Sorry, your browser (" + BrowserDetect.browser + ' ' + 
            BrowserDetect.version + ' on ' + BrowserDetect.OS + ") is not compatible with the " + shortTitle + " site. Please refer to the list of supported browsers and operating systems on this page.";
        document.write('<p style=\"color:red\">' + msg + '</p>');
        alert (msg);
    }
    else if(checkDhairBrowserCompatibility() == "yes"){
        var msg = "<strong>Your Browser</strong>:<br />We see that you're using " + 
            BrowserDetect.browser + ' ' + BrowserDetect.version + ' on ' + BrowserDetect.OS + ". This browser works with the " + shortTitle + " site. If you have any problems, please contact us.";
        document.write('<p>' + msg + '</p>');
    }
    else if(checkDhairBrowserCompatibility() == "maybe"){
        var msg = "From what we can tell you're using " + BrowserDetect.browser + 
            ' ' + BrowserDetect.version + ' on ' + BrowserDetect.OS + ". Your browser isn't officially supported, but may work fine with the " + shortTitle + " site. If you have any problems, please try one of the officially supported browsers.";
        document.write('<p>' + msg + '</p>');
    }


