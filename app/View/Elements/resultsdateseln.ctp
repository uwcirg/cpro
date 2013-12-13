<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/
?>
<script type="text/javascript">
$(function() {
    $( "#start-date" ).datepicker({
        changeMonth: true,
        changeYear: true
    });
    $( "#end-date" ).datepicker({
        changeMonth: true,
        changeYear: true
    });
});
</script>

<div id="dates" class="subsection">
<fieldset>
    <legend>Date Range
    </legend>
    <div id="chartdatesdropdown">
        <p>Show:
        <select id='date-range' style="display:inline;">
        </select>
        </p>
	<p style="text-align:center;">
        <a href="#" 
            onclick="$('#chartdatesdropdown').hide();$('#chartdatescalendar').show(); return false;"
            >Specify custom range</a>
	</p>
    </div>
    <div id="chartdatescalendar" style="display:none;">
        <div id="date-range-calendars"><p>
            <label for="start-date">Start: </label>
            <input id="start-date" size="12"/><br/>

            <label for="end-date">End: </label>
            <input id="end-date" size="12"/>
        </p></div>
        <br/>
	<div class="spacer"></div>
        <a  href="#"
            onclick="$('#chartdatescalendar').hide();$('#chartdatesdropdown').show(); return false;"
        >Use standard date ranges</a>
    </div>

</fieldset>
</div>


