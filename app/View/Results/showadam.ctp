<?php
/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/

 echo "<h1>$item</h1>";
 echo "<div id='coaching'><span class='coaching'><p>This graph displays your past responses on questions about $item. This scale was developed by __ to measure __ and is commonly used in clinics that serve cancer patients. <a href='#'>Learn more</a></p><p>Click on a scale on the right to see your results in that area and learn more about it.</p></span></div>";
 
 ?>
 
 <div id='graph'>
   
   <span class="options">View data for <select><option>all surveys</option><option>the last two surveys</option></select> as a <select><option>line chart</option><option>bar chart</option><option>list</option><option>table</option></select>.<br/>&nbsp;</span>
   
   
   <div id="placeholder" style="margin:0px;padding:0px;float:left;width:500px;height:300px;"></div>
   <div id='labels'>
     <b>Quality of Life Scales:</b><br/>

  </div>
  <br/ style="clear:both;">&nbsp;
  <?php
  

  
  ?>
  </div>
  <script id="source" language="javascript" type="text/javascript">

  
$.fn.extend({
  arrayFind: function(match) {
    selected = [];
    matcheds = this;
    // try each item in the array to see if it matches
    this.each(function(matched) {
      matched = matcheds[matched];
      matchThis = true;
      // run through all properties in the matcher
      for (var prop in matched) {
        if ( match.prop != matched.prop) {
          matchThis = null;
          break;
        }
      }
      if (matchThis) {
        selected.push(matched);
      }
    });
    
    return selected;
  } });
  
  $(function () {
      var symptoms = [<?php foreach($subitems as $subitem) { print "'$subitem', "; }?>];
      
      var times = [(new Date("2008/02/09")).getTime(),
                   (new Date("2008/03/01")).getTime(),
                   (new Date("2008/04/15")).getTime(),
                   (new Date("2008/05/27")).getTime(),
                   (new Date("2008/07/01")).getTime()];
      
      data = [];
      
      for(var symptom in symptoms) {
        var row = []
        for (var time in times) {
          row.push([times[time], Math.random() * 5]);
        }
        data.push({
          label: symptoms[symptom],
          data: row
        });
      }
      
      function labelForSymptom(symptom) {
        var labelS = "<input id='${subitem}' name='${subitem}' type='checkbox'/ CHECKED>" +
        "<span class='symptom'><label class='label' for='${subitem}'>${subitem}</label>"+
        "<span class='coaching'><p>Based on your answers on the ESRA-C II questionnaire, you may to have some difficulty concentrating and staying focused on tasks.</p><p><ul><li>This happens sometimes due to the effect of hormone changes on the brain, about 10% of our patients like yourself have reported similar symptoms.</li><li>There are things you can do at home to help deal with this symptom and there may be things that your clinical team will recommend for you.</li><li>Be sure to talk with your clinical team about this during today’s or tomorrow’s visit so they can suggest ways to deal with this symptom. We suggest saying something like this: \"I’ve noticed that I cannot concentrate very long or stay focused on things I’m doing. It started <input/> (fill in the blank with when you first noticed the symptom). It has gotten <input/> (better or worse) since I saw you last. What do you recommend to deal with this?</li></ul></p>";
        
        var labelT = $.template(labelS);
        return labelT.apply({subitem: symptom}); 
      }
      
      function checkClicked() {
        var newData = [];
        $("#labels input:checked").map(function() {
          
        })
      }
      
      function rePlot(data) {
        $.plot($("#placeholder"), data, { legend: { show: true,
                                                  margin: 3,
                                                  backgroundColor: "#fff",
                                                  container: $("#labels"),
                                                  labelFormatter: labelForSymptom },
                                        xaxis: { mode: "time" } });
        $('#labels input').click(checkClicked);
      }
      rePlot(data);
  });
  </script>

  
  
  
 <script type="text/javascript">
  
  $(function () {
    $("#labels tr").popup({label:"tr", message: ".hidden"})
                 .symptomCoach({ label: "td.legendLabel", backToStart: "div#labels b"}); });
  
 </script>
