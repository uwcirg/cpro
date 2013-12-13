<?php

/**
 *
 * Tidy HTML Configuration
 * http://tidy.sourceforge.net/docs/quickref.html
 *
    *
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause
    *
 *
 */
Configure::write('tidy.config', array(
// $tidyConfig = array(

    // HTML, XHTML, XML Options
    'doctype' => 'strict',
    'clean' => true,
    'drop-empty-paras' => true,
    'drop-font-tags' => true,
    'drop-proprietary-attributes' => false,
    'fix-backslash' => true,
    'fix-bad-comments' => true,
    'fix-uri' => true,
    'literal-attributes' => true,
    'logical-emphasis' => true,
    'merge-divs' => 'auto',
    'merge-spans' => 'auto',
    'new-empty-tags' => 'i',
    'output-xhtml' => true,
    'quote-marks' => true,
    'replace-color' => true,
    'show-body-only' => true,
    'word-2000' => true,

    // Diagnostics Options Reference
    // 'show-warnings' => false,
    // 'show-errors' => 0,

    // Pretty Print Options
    'break-before-br' => true,
    'indent' => true,
    'sort-attributes' => true,
    'tab-size' => 4,

    // Turn off line-wrapping to fix broken substitution functions
    'wrap' => 0,

));

Configure::write('tidy.falsePositives', array(
    // These are not applicable to incomplete pages
    // and are not acted on because of the HTML Tidy "show-body-only" setting
    'missing <!DOCTYPE> declaration',
    "plain text isn't allowed in <head> elements",
    'inserting implicit <body>',
    "inserting missing 'title' element",
));

class TidyBetterMessages extends Tidy {

    /**
    *   @param $ignoreFalsePositives - control whether messages about complete pages appear or not
    *   @param $type - string or array representing desired message types
    */
    public function getMessages($ignoreFalsePositives=true, $type=null){
        if (!$this->errorBuffer)
            return array();

        preg_match_all(
            '/^(?:line (\d+) column (\d+) - )?((?:Info)|(?:Warning)|(?:Error)): (.+?)$/m',
            $this->errorBuffer,
            $tidyErrors,
            PREG_SET_ORDER
        );

        $tempTidyMessages = array(
            'info' => array(),
            'warning' => array(),
            'error' => array(),
        );

        if (!$type)
            $type = array_keys($tempTidyMessages);

        else if (is_string($type))
            $type = (array)$type;


        // Reformat messages by type
        foreach($tidyErrors as $error){
            if (
                array_key_exists(strtolower($error[3]), $tempTidyMessages)
                and !(
                    $ignoreFalsePositives
                    and $this->getOpt('show-body-only')
                    and in_array($error[4], Configure::read('tidy.falsePositives'))
                )

            ){
                $temp = array(
                    'message' => $error[4]
                );
                if ($error[1])
                    $temp['line'] = (int)$error[1];

                if ($error[2])
                    $temp['column'] = (int)$error[2];
                array_push($tempTidyMessages[strtolower($error[3])], $temp);
            }

        $tidyMessages = array();
        foreach($tempTidyMessages as $messageType => $messageList)
            // Allow only desired message types
            if ($messageList and in_array($messageType, $type))
                $tidyMessages[$messageType] = $messageList;
        }

        return $tidyMessages;
    }
}


?>
