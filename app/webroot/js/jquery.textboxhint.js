/**
 * Form text box hints.
 *
 * This plug-in will allow you to set a 'hint' on a text box or
 * textarea.  The hint will only display when there is no value
 * that the user has typed in, or that is default in the form.
 *
 * You can define the hint value, either as an option passed to
 * the plug-in or by altering the default values.  You can also
 * set the hint class name in the same way.
 *
 * Examples of use:
 *
 *     $('form *').textboxhint();
 *
 *     $('.date').textboxhint({
 *         hint: 'YYYY-MM-DD'
 *     });
 *
 *     $.fn.textboxhint.defaults.hint = 'Enter some text';
 *     $('textarea').textboxhint({ classname: 'blurred' });
 *
 * @copyright Copyright (c) 2009,
 *            Andrew Collington, andy@amnuts.com
 * @license New BSD License
 */
(function($) {
    $.fn.textboxhint = function(userOptions) {
        var options = $.extend({}, $.fn.textboxhint.defaults, userOptions);
        return $(this).filter(':text,textarea').each(function(){
            if ($(this).val() == '') {
                //fixme read orig text color so we know what to revert to
                $(this).focus(function(){
                    if ($(this).attr('typedValue') == '') {
                        $(this).removeClass(options.classname).val('');
                        $(this).css('color', 'black');
                        $(this).css('font-style', 'normal');
                    }
                }).blur(function(){
                    $(this).attr('typedValue', $(this).val());
                    if ($(this).val() == '') {
                        $(this).addClass(options.classname).val(options.hint);
                        $(this).css('color', options.hintTextColor);
                        $(this).css('font-style', 'italic');
                    }
                }).blur();
            }
        });
    };

    $.fn.textboxhint.defaults = {
        hint: 'Please enter a value',
        classname: 'hint',
        hintTextColor: 'gray'
    };
})(jQuery);

