/**
    * 
    * Copyright 2013 University of Washington, School of Nursing.
    * http://opensource.org/licenses/BSD-3-Clause 
    *
*/   
 
// Journals Controller:
// just a wrapper around some old, ugly code

    function JournalsController(options) {
        var journals = this;
        this.defaults = {
            baseUrl: "/journals/",// ends in slash
            editable: false,
            patientId: 0
        };

        this.readActions = {
            0: "listForReadOnly/",
            1:  "index/"
        };

        this.opts = $.extend(true, this.defaults, options);
        this.opts.readAction = this.readActions[+this.opts.editable];
        var opts = this.opts;

        this.setDates = function(start, end) {
            if(!start) {
                start = 0;
            }
            opts.start = start;
            opts.end = end;

            $("#journal-entries-container tr.journal-entry")
                .each(function(i, tr) {
                    time = $(tr).find("td.journal-date").attr('id');
                    if(!time || time === "") {
                        // rows like the new entry row
						$(tr).hide();
                    } else if (time < start || (end && time > end)) {
                        $(tr).hide();
                    } else {
                        $(tr).show();
                    }
                });
        };

        // load all displayable journals from the server 
        // and set up jQuery hooks for them    
        this.load = function(callback) {
            // returns content of journals/index using an HTTP GET request
            $.get(journals.opts.baseUrl+
                  journals.opts.readAction+
                  journals.opts.patientId, 
                null,
                // callback fxn executed whenever data loaded successfully
                function(data, status) { 
                    //load data
                    $("#journal-entries-container").html(data);
                    $("tr#new-journal-entry").hide();
                    
                    // add callbacks if it is editable
                    if(journals.opts.editable) {
                   
                        $("tr.journal-entry .actions a.delete").click(
                            function() {
                                if(!confirm(
                                    "Do you want to delete this entry permanently?")) {
										return false;
									}

                                $.post(journals.opts.baseUrl + "delete/" + 
                                    $(this).parents("tr").find('.text').attr('id'),
                                        {
                                                "data[AppController][AppController_id]" : acidValue
                                        });

                                $(this).parents("tr").remove();
                                return false;
                        });
                        
                        $("tr.journal-entry a.edit").click(function() {
                            var $row = $(this).parents("tr"),
                               $cell = $row.find("td.journal-text"),
                                  id = $row.find("p.text").attr('id'),
                                text = $cell.find("p").text(),
                               $node = $("<textarea/>").html(text).addClass("text").attr({id:id});

                            $(this).hide();
                            $cell.html($node);
                            $("input.submit", $row).show();
                        });

                        $("a.addNew").click(
                            function() {
                                // FIXME: doesn't work in IE
                                $("tr#new-journal-entry").show();
                                $("tr#add-journal-entry").hide();
                                $("tr#new-journal-entry input.date").datepicker(
                            {dateFormat: "m/d/yy", defaultDate: +1, maxDate: +1}).focus();
                                return false;
                            }
                        );
                        $("tr.journal-entry input.submit").click(function() {
                            var $row = $(this).parents("tr");
                            $.post(journals.opts.baseUrl+"updateText",
                                { 
                                    "data[JournalEntry][id]"   : $row.find(".text").attr('id'),
                                    "data[JournalEntry][text]" : $row.find(".text").val(),
			                        "data[AppController][AppController_id]" : acidValue
                                }, 
                                journals.load);
                        });

                        $("tr#new-journal-entry input.submitnew").click(
                            function() {
                                var $row = $(this).parents("tr");
                                $.post(journals.opts.baseUrl+"create/",
                                { 
                                    "data[JournalEntry][text]" : $row.find(".text").val(),
                                    "data[JournalEntry][date]" : $row.find(".date").val(),
			                        "data[AppController][AppController_id]" : acidValue
                                }, 
                                journals.load);
                            }
                        ); 
                    } // end if editable

                    // setup newly-added dom items
                    if(callback) {
                        callback();
					}

                    journals.setDates(opts.start, opts.end);
                }
            );
        }; // function journals.load(callback) 

        this.initialize = function(callback) {
            this.load(callback);
        };
                  
        return this;
    } //function JournalsController(options) {


// Much of this can probably be removed if we switch to Highcharts. Data will need to be called, but much could be removed/changed.
function ChartsController(options, allData, metaData) {
// data has empty arrays for subscales
// not currently selected, use allData
// to get the full data for all scales.
this.allData = allData;
this.metaData = metaData;
this.defaults = {};
this.opts = $.extend(true, this.defaults, options);

// get the index in the data array of the original subscale
this.metaData.originalSubscale = $.first(allData, function(row) {
    if(row.subscale_id == metaData.originalSubscale) {
        return true;
    } else {
        return false;
    }
})[0];

this.timepoints = $.map(
    this.allData[this.allData.length-1].data, 
    function(cell) {
        return cell[0];
    });

    
this.lineOptions = { 
    xaxis: { 
        mode: "time", min: null, max: null,
        ticks: function(axis) {
            var ticks = [],
                range = axis.max - axis.min,
                msPerDay = 1000 * 60 * 60 * 24,
                days = range / msPerDay,
                step = days > 7 ? range/7 : msPerDay;

            for(var i=0; i<days+1; i++) {
                ticks[ticks.length] = axis.min + i * step;
            }
            return ticks;
        }
    },
    yaxis: { 
        min: this.metaData.min,
        max: this.metaData.max,
        minTickSize: 1,
        tickFormatter: function(n) { 
            if(n==this.min) {
                return "Better " + n;
            } else if(n==this.max) {
                return "Worse " + n;
            } else {
                return "" + n;
            }
        },
        ticks: function(axis) {
            var d = Math.ceil( (axis.max - axis.min) / 5),
            i = Math.ceil(axis.min),
            res = [];
            while(i <= axis.max + d) {
                res[res.length] = i;
                i+=d;
            }
            return res;
        }
    },
    points: { show: true },
    lines: { show: true },
    legend: { show: false },
    grid: { backgroundColor: "#ffffff",
            hoverable: true,
            clickable: true
            }
};

if(this.metaData.critical !== null && this.metaData.critical > 0) {
    this.lineOptions.grid.coloredAreas = [{ color: "#ff9999",
                             y1: this.metaData.critical,
                             y2: this.metaData.max}];
}

// Bar optons-only settings
this.barOptions = $.clone(this.lineOptions);
this.barOptions.lines.show = false;
this.barOptions.points.show = false;
this.barOptions.bars = {show: true, barWidth: 1, fill:1.0};
this.barOptions.xaxis.mode = null;

// Line options-only settings
var label_id = 0;
this.lineOptions.legend = {
    container: $("#graph-legend"),
    labelFormatter: function(label) {
        return '<input id="' + label_id +
            '" class="symptom" type="checkbox"/><label for="' +
            label_id++ + '">' + label + '</label>';
    }
};
this.lineOptions.xaxis.tickFormatter = function(time) {
    return new Date(time).format("m/d");
};

// pre-assign colors so they remain constant
$.each(this.allData, function(id, row) {
    row.color = id;
});


this.lineData = [];
this.barData  = [];
$(this.opts.table).html(this.tableHTML());

this.initialize = function() {
    // initial data formatting
    // pass true so customize_chart isn't logged
    this.setSubscales([this.metaData.originalSubscale], true);

    // now select the checkbox on the interface
    $("input.symptom#" + this.metaData.originalSubscale).attr({checked: true});
    var previousPoint = null;
};

return this;
} //function ChartsController(options, allData, metaData) {

$.extend(ChartsController.prototype, {

// build dom elements for the table with classes 
// by date and symptom so they can be hidden later.
showTooltip: function(x, y, contents) {
    $("<div id='chart-tooltip'>" + contents + "</div>").css({
        position:   'absolute',
        display:    'none',
        top:        y + 5,
        left:       x + 5,
        border:     '1px solid #999',
        padding:    '2px',
        'background-color': '#ccc',
        opacity:    0.8
    }).appendTo("body").fadeIn(200);
},
tableHTML: function() {
    var rows = [];
    for(var ss_id in this.allData) {
        row = this.allData[ss_id];
        var cells = $.map(row.data, function(cell){
            // add classes
            if(cell[1] === undefined) {
                return "<td class='"+cell[0]+"'> </td>";
            } else {
                return "<td class='"+cell[0]+"'>" + cell[1] + "</td>";
            }
        }).join('');
        cells = "<th>" + row.label + "</th>" + cells;
        rows.push("\t<tr id='subscale_"+ss_id+"'>" + cells + "</tr>\n");
    }
    // var body = "<tbody>\n" + rows.join('') + "</tbody>\n";
    var body = $("<tbody></tbody>").append(rows.join(''));

    // header row
    var header = $("<thead><tr><th/></tr></thead>");
    $.each(this.timepoints, function(i, timestamp) {
        var date = new Date(timestamp).format("m/d");
        $("<th class='"+timestamp+"'/>").append(date).appendTo($("tr", header));
    });

    return $("<table id='table-view'/>").append(header).append(body);
    // return "<table>\n" + header + body + "</table>\n";
},

getLineData: function() {
    // Line chart: select data rows for these subscales
    var lineData = [];
    charts = this;

    for(var id=0; id<charts.allData.length; id++) {
        id = parseInt(id);
        if($.inArray(id, this.subscale_ids) == -1) {
            // don't chart data unless among selected subscales
            lineData[lineData.length] = {
                label: charts.allData[id].label,
                data:  [],
                color: charts.allData[id].color
            };
        } else {
            lineData[lineData.length] = charts.allData[id];
            // rawData = charts.allData[id];
            // rawData.data = $.filterNot(rawData.data, function(i, x) { return x == null || x[1] == null; });
            // lineData[lineData.length] = rawData
        }
    }
    
    // filter empty data points from line data
    for(var i = 0; i<lineData.length; i++) {
        lineData[i].data = $.filterNot(lineData[i].data, function(j, x) { return x === null || x[1] === null; });
    }
    return lineData;
},

getBarData: function(lineData) {
    // Bar chart: transpose line data time stamps to indices
    // counts what position row is among rows with shown data
    var barPosition = 0;
    var barData = [];
    // assume all series have the same time points
    for(var row=0; row<lineData.length; row++) {
        var item = lineData[row];
        var row_data = lineData[row].data;
        if(row_data && row_data.length > 0) {
            var i = 0,
                bar_data = [];
            for(var time_i=0; time_i < this.timepoints.length; time_i++) {
                var time = this.timepoints[time_i];
                var bar_x = time_i * (this.subscale_ids.length + 1) + barPosition;
                if(i < row_data.length && row_data[i][0] == time) {
                    bar_data[time_i] = [bar_x, row_data[i++][1]];
                } else {
                    bar_data[time_i] = [bar_x, 0]; //FIXME: set undefined
                }
            }
            item.data = bar_data;
            barPosition++;
        }
        barData[row] = item;
    }

    // generate bar chart tick marks with formatted time
    var bar_x_ticks = [];
    $.each(this.timepoints, function(i, time) {
        bar_x_ticks[bar_x_ticks.length] = 
            [i * (1 + barPosition) + (barPosition / 2) + .5, new Date(time).format("m/d")];
    });

    this.barOptions.xaxis.ticks = bar_x_ticks;
    this.setBarXaxis(this.lineOptions.xaxis.min,
                     this.lineOptions.xaxis.max);
    return barData;
},

setSubscales: function(subscale_ids, dontLogChartCustomization) {
    charts = this;
    this.subscale_ids = subscale_ids;
    this.lineData = this.getLineData();
    this.barData = this.getBarData($.clone(this.lineData));
    var subsNowChecked = '';

    // Table: show and hide subscales
    $(this.opts.table + " tbody tr").hide();
    $.each(subscale_ids, function(i, id) {
        //alert("id " + id + " checked: " + $("input.symptom#" + id).attr("checked"));
            subsNowChecked += charts.allData[id].subscale_id + ",";
        $("tr#subscale_" + id).show();
    });
   
    if (dontLogChartCustomization !== true){
        if (subsNowChecked !== ''){
            // get rid of the trailing comma
            subsNowChecked = 
                subsNowChecked.substr(0, subsNowChecked.length - 1); 
        }
        $.ajax({ url: "../customize_chart/"+subsNowChecked });
    } 

    // Replot the graphs
    this.plot();
},

setDates: function(start, end) {
    // don't set a date that wouldn't include at least some data
    /*
    if(start > this.timepoints[this.timepoints.length-1]) {
        start = null;
    }
    if(end < this.timepoints[0]) {
        end = null;
    }
    */

    this.lineOptions.xaxis.min = start;
    this.lineOptions.xaxis.max = end;

    this.setBarXaxis(start, end);
    this.setTableColumns(start, end);
    this.plot();
},

// returns indices for timepoints within date range
timepointRange: function(start, end) {
    startIndex = 0;
    endIndex = this.timepoints.length - 1;
    if(start) {
        startIndex = $.first(this.timepoints, function(timepoint) {
            return timepoint > start;
        })[0];
    }

    if(end) {
        endIndex = $.last(this.timepoints, function(timepoint) {
            return timepoint < end;
        })[0];
    }
    return [startIndex, endIndex];
},

setTableColumns: function(start, end) {
    var range = this.timepointRange(start, end);
    var startIndex = range[0];
    var endIndex   = range[1];

    $.each(this.timepoints, function(i, timepoint) {
        if(i >= startIndex && i <= endIndex) {
            $("."+timepoint).show();    
        } else {
            $("."+timepoint).hide();
        }
    });
},

setBarXaxis: function(start, end) {
    var range = this.timepointRange(start, end);
    var startIndex = range[0];
    var endIndex   = range[1];
    this.barOptions.xaxis.min = startIndex * (this.subscale_ids.length + 1);
    this.barOptions.xaxis.max = (endIndex + 1) * (this.subscale_ids.length + 1);
},

plot: function() {
    $.plot($(this.opts.lineGraph), this.lineData, this.lineOptions);
    $.plot($(this.opts.barGraph),  this.barData,  this.barOptions);

    // after first plot, don't reshow the legend
    this.lineOptions.legend = { show: false };

    $(this.opts.barGraph).bind( "plothover", this.plotHover);
    $(this.opts.lineGraph).bind("plothover", this.plotHover);
},
plotHover: function(e, pos, item) {
    if(item) {
        if(this.hoveredPoint != item.datapoint) {
            this.hoveredPoint = item.datapoint;

            $("#chart-tooltip").remove();
            var x = new Date(item.datapoint[0]).format("m/d"),
                y = item.datapoint[1];

            $.charts.showTooltip(item.pageX, item.pageY, item.series.label + ": " + y + " on " + x);
        }
    } else {
        $("#chart-tooltip").fadeOut(200);
        this.hoveredPoint = null;
    }
}
});//$.extend(ChartsController.prototype, {

function SubscalesController(options) {
var controller = this;

this.defaults = {
    callbacks: [],
    initial:   [],
    div: "#graph-legend"
};

this.opts = $.extend(this.defaults, options);

this.selectSubscales = function(subscales) {
    $(this.opts.selector);
};

this.setSubscales = function() {
    var subscales = $.map($('input.symptom:checked'), function(input) {
        return parseInt($(input).attr('id'));
    });
    $.each(controller.opts.callbacks, function(i, callback) {
        if(callback && callback.setSubscales) {
            callback.setSubscales(subscales);
        }
    });
};

this.initialize = function() {
    var subscales = this;
    // Initialization: attach events and call first subscale
    $(this.opts.div + " td.legendLabel").click(function() {
        subscales.setSubscales();
    });

    $(this.opts.div + " td.legendColorBox").click(function() {
        $(this).siblings("td.legendLabel").find("input").click();
        subscales.setSubscales();
    });
};

return this;
} //function SubscalesController(options) {


function DatesController(options) {
var dates = this;

var now = new Date().getTime();
var today = new Date().getDate();
this.defaults = {
    callbacks: [],
    select:     "select#date-range",
    startDate:  "input#start-date",
    endDate:    "input#end-date",
    options:    [ ["All dates", null, null ],
                  ["Two weeks", new Date().setDate(today-14), now],
                  ["Month", new Date().setDate(today-31), now],
                  ["Three Months", new Date().setDate(today-92), now],
                  ["Six Months", new Date().setDate(today-183), now],
                  ["Year", new Date().setDate(today-365), now]]
};
this.opts = $.extend(this.defaults, options);
this.startDate  = null;
this.endDate    = null;
this.advanced   = false;

this.setDates = function(start, end) {
    $.each(this.opts.callbacks, function(i, callback) {
        if(callback && callback.setDates) {
            callback.setDates(start, end);
        }
    });
    dates.startDate = start;
    dates.endDate = end;
};

this.initialize = function() {
    // remove old options
    $(dates.opts.select).find('option').remove();
    // create select options
    $(this.opts.options).each(function(i, option) {
        $(dates.opts.select).append(
            $("<option optionId='"+i+"'/>").append(option[0])
        );
    });
    // bind event for select
    $(dates.opts.select).change(function(e) {
        optionId = $(dates.opts.select).find("option:selected").attr('optionId');
        option = dates.opts.options[optionId];
        dates.setDates(option[1], option[2]);
    });

    // bind events for start/end date
    function setAdvancedDates() {
        var startDate = $(dates.opts.startDate).val();
        var endDate   = $(dates.opts.endDate).val();

        if(startDate && startDate !== "") {
            startDate = new Date(startDate).getTime();
        } else {
            startDate = null;
        }

        if(endDate && endDate !== "") {
            endDate = new Date(endDate).getTime();
        } else {
            endDate = null;
        }
        dates.setDates(startDate, endDate);
    }
    $(this.opts.startDate).datepicker({dateFormat:"m/d/yy", maxDate:0})
        .change(setAdvancedDates);
    $(this.opts.endDate).datepicker({dateFormat:"m/d/yy", maxDate:0})
        .change(setAdvancedDates);
};

return this;
} //function DatesController(options) {

$.extend(DatesController.prototype,
{

});

