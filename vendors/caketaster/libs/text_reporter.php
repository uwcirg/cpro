<?php

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count. For command
 *    line use. I've tried to make it look like JUnit,
 *    but I wanted to output the errors as they arrived
 *    which meant dropping the dots.
 *	  @package SimpleTest
 *	  @subpackage UnitTester
 */
class TextReporter extends SimpleReporter {

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start.
     *    @access public
     */
    function TextReporter() {
        $this->SimpleReporter();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/plain');
        }
        print "$test_name\n";
        flush();
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            print "OK\n";
        } else {
            print "FAILURES!!!\n";
        }
        print "Test cases run: " . $this->getTestCaseProgress() .
                "/" . $this->getTestCaseCount() .
                ", Passes: " . $this->getPassCount() .
                ", Failures: " . $this->getFailCount() .
                ", Exceptions: " . $this->getExceptionCount() . "\n";
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print $this->getFailCount() . ") $message\n";
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
        print "\n";
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        print "$message\n";
        flush();
    }
}

/**
 *    Runs just a single test group, a single case or
 *    even a single test within that case.
 *	  @package SimpleTest
 *	  @subpackage UnitTester
 */
class SelectiveReporter extends SimpleReporterDecorator {
    var $_just_this_case =false;
    var $_just_this_test = false;
    var $_within_test_case = true;

    /**
     *    Selects the test case or group to be run,
     *    and optionally a specific test.
     *    @param SimpleScorer $reporter    Reporter to receive events.
     *    @param string $just_this_case    Only this case or group will run.
     *    @param string $just_this_test    Only this test method will run.
     */
    function SelectiveReporter(&$reporter, $just_this_case = false, $just_this_test = false) {
        if (isset($just_this_case) && $just_this_case) {
            $this->_just_this_case = strtolower($just_this_case);
            $this->_within_test_case = false;
        }
        if (isset($just_this_test) && $just_this_test) {
            $this->_just_this_test = strtolower($just_this_test);
        }
        $this->SimpleReporterDecorator($reporter);
    }

    /**
     *    Compares criteria to actual the case/group name.
     *    @param string $test_case    The incoming test.
     *    @return boolean             True if matched.
     *    @access protected
     */
    function _isCaseMatch($test_case) {
        if ($this->_just_this_case) {
            return $this->_just_this_case == strtolower($test_case);
        }
        return false;
    }

    /**
     *    Compares criteria to actual the test name.
     *    @param string $method       The incoming test method.
     *    @return boolean             True if matched.
     *    @access protected
     */
    function _isTestMatch($method) {
        if ($this->_just_this_test) {
            return $this->_just_this_test == strtolower($method);
        }
        return true;
    }

    /**
     *    Veto everything that doesn't match the method wanted.
     *    @param string $test_case       Name of test case.
     *    @param string $method          Name of test method.
     *    @return boolean                True if test should be run.
     *    @access public
     */
    function shouldInvoke($test_case, $method) {
        if ($this->_within_test_case && $this->_isTestMatch($method)) {
            return $this->_reporter->shouldInvoke($test_case, $method);
        }
        return false;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_case, $size) {
        if ($this->_isCaseMatch($test_case)) {
            $this->_within_test_case = true;
        }
        if ($this->_within_test_case) {
            $this->_reporter->paintGroupStart($test_case, $size);
        }
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_case) {
        if ($this->_within_test_case) {
            $this->_reporter->paintGroupEnd($test_case);
        }
        if ($this->_isCaseMatch($test_case)) {
            $this->_within_test_case = false;
        }
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_case     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_case) {
        if ($this->_isCaseMatch($test_case)) {
            $this->_within_test_case = true;
        }
        if ($this->_within_test_case) {
            $this->_reporter->paintCaseStart($test_case);
        }
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_case     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_case) {
        if ($this->_within_test_case) {
            $this->_reporter->paintCaseEnd($test_case);
        }
        if ($this->_isCaseMatch($test_case)) {
            $this->_within_test_case = false;
        }
    }
}

?>