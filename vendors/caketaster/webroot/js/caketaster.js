var Caketaster = new function() 
{
    this.initialize = function()
    {
        Caketaster.Behaviors();
    }
    
    /**
     * All custom behaviors we want to add to our elements are handled in this 
     * part of the Caketaster Site Controller
     **/
    this.Behaviors = function()
    {
        for (var fct in Caketaster.Behaviors)
        {
            if (typeof(Caketaster.Behaviors[fct])=='function' && !(Function[fct]))
            {
                Caketaster.Behaviors[fct]();
            }
        }
    }
    
    this.Behaviors.collapseAllTestCases = function()
    {
        $('ul ul').hide();
    }
    
    this.Behaviors.toggleTriggers = function()
    {
        $('h2').click(function(){$('ul', this.parentNode).toggle();});
    }
    
    this.Behaviors.addJsControls = function()
    {
        $('form').after('<div id="js-control"><a href="#">Expand All</a> <a href="#">Expand All Failed</a> <a href="#">Expand All Passed</a></div>');
        $('a', '#js-control').attr('class', 'expand').click(Caketaster.Tests.jsControl);
    }
    
    this.Tests = function(){};
    this.Tests.jsControl = function(e)
    {
        e.preventDefault();
        
        var commands = this.innerHTML.split(' ');
        
        if (!commands[2])
            var selector = 'ul ul';
        else
            var selector = 'ul .test-'+commands[2].toLowerCase()+' ul';

        if (commands.shift()=='Expand')
        {
            $(selector).show();
            this.innerHTML = 'Collapse '+commands.join(' ');
            $(this).attr('class', 'collapse');
        }
        else
        {
            $(selector).hide();
            this.innerHTML = 'Expand '+commands.join(' ');
            $(this).attr('class', 'expand');
        }
        
        return false;
    }
}

$(document).ready(Caketaster.initialize);