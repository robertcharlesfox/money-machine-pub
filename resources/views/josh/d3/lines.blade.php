@extends('josh/layouts/nobottom')

{{-- Page title --}}
@section('title')
Money Flow Analysis
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
<style>

body {
  font: 10px sans-serif;
}

.axis path,
.axis line {
  fill: none;
  stroke: #000;
  shape-rendering: crispEdges;
}

.x.axis path {
}

.line {
  fill: none;
  stroke: steelblue;
  stroke-width: 1.5px;
}

</style>

@stop

{{-- Page content --}}
@section('content')
    <!-- Container Section Start -->
    <div class="container">
        <!--Content Section Start -->
        <h2></h2>
        <div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="panel-group panel-accordion faq-accordion">
                        <div id="faq">
                            <div class="mix category-1 col-lg-12 panel panel-default" data-value="1">
                                <div class="panel-body">
                                    <h4 class="panel-title">
                                        <strong class="c-gray-light">Options:</strong>
                                    </h4>
                                    <div class="row">
                                        <!-- Select Basic -->
                                        <div class="form-group">
                                          <div class="col-md-4">
                                          <label class="control-label" for="contest-id">Contest</label>
                                            <select id="contest-id" name="contest-id" class="form-control">
                                              @foreach ($contests as $contest)
                                              <option value="{{ $contest->id }}">{{ $contest->category . ':' . $contest->name }}</option>
                                              @endforeach
                                            </select>
                                          </div>

                                          <div class="col-md-4">
                                          <label class="control-label" for="line-index">Line Index</label>
                                            <select id="line-index" name="line-index" class="form-control">
                                              <option value="last_price">Last Price</option>
                                              <option value="ratio_of_ratios">Ratio $-¢</option>
                                              <option value="market_support_net_dollars">Net $</option>
                                              <option value="market_support_net_price_spread">Spread ¢</option>
                                              <option value="change_todays_volume">Volume (Total)</option>
                                              <option value="new_shares">Volume (New Shares)</option>
                                              <option value="swapped_shares">Volume (Swapped Shares)</option>
                                              <option value="change_price">Price Change</option>
                                              <option value="change_yes_price">Change YES ¢</option>
                                              <option value="change_yes_dollars">Change YES $</option>
                                              <option value="change_no_price">Change NO ¢</option>
                                              <option value="change_no_dollars">Change NO $</option>
                                              <option value="change_net_price_spread">Change Spread ¢</option>
                                              <option value="change_net_dollars">Change Net $</option>
                                              <option value="market_support_yes_side_price">Market YES ¢</option>
                                              <option value="market_support_yes_side_dollars">Market YES $</option>
                                              <option value="market_support_no_side_price">Market NO ¢</option>
                                              <option value="market_support_no_side_dollars">Market NO $</option>
                                              <option value="market_support_ratio_price">Ratio ¢</option>
                                              <option value="market_support_ratio_dollars">Ratio $</option>
                                            </select>
                                          </div>

                                        <!-- Select Basic -->
                                          <div class="col-md-2">
                                          <label class="control-label" for="search-take">Take</label>
                                            <select id="search-take" name="search-take" class="form-control">
                                              <option value="10">10</option>
                                              <option value="50">50</option>
                                              <option value="100">100</option>
                                              <option value="200">200</option>
                                              <option value="300">300</option>
                                              <option value="400">400</option>
                                              <option value="500">500</option>
                                              <option value="1000">1000</option>
                                              <option value="2000">2000</option>
                                            </select>
                                          </div>

                                        <!-- Select Basic -->
                                          <div class="col-md-2">
                                          <label class="control-label" for="search-skip">Skip</label>
                                            <select id="search-skip" name="search-skip" class="form-control">
                                              <option value="0">0</option>
                                              <option value="50">50</option>
                                              <option value="100">100</option>
                                              <option value="200">200</option>
                                              <option value="300">300</option>
                                              <option value="400">400</option>
                                              <option value="500">500</option>
                                              <option value="1000">1000</option>
                                              <option value="2000">2000</option>
                                            </select>
                                          </div>

                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                        <!-- Select Basic -->
                                        <div class="form-group">
                                          <div class="col-md-4">
                                          <label class="control-label" for="question-id">Binary Question</label>
                                            <select id="question-id" name="question-id" class="form-control">
                                              <option value="none">N/A</option>
                                              <option value="all">All Binaries</option>
                                              @foreach ($binary_questions as $question)
                                              <option value="{{ $question->id }}">{{ $question->question_ticker }}</option>
                                              @endforeach
                                            </select>
                                          </div>

                                        </div>
                                    </div>
                                    <br />
                                    <div class="row">
                                      <div class="col-md-2 col-md-offset-5">
                                        <button id="get-button" onclick="getLineData()" class="btn btn-success">
                                          Get Data
                                        </button>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="graph-here" class="row"></div>
        <!-- //Content Section End -->
    </div>
@stop

{{-- page level scripts --}}
@section('footer_scripts')
<script src="//d3js.org/d3.v3.min.js"></script>

<script>
var margin = {top: 20, right: 80, bottom: 30, left: 50},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

var parseDate = d3.time.format("%Y-%m-%d %H:%M:%S").parse;

var x = d3.time.scale()
    .range([0, width]);

var y = d3.scale.linear()
    .range([height, 0]);

var color = d3.scale.category10();

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");

var line = d3.svg.line()
    .interpolate("basis")
    .x(function(d) { return x(d.date); })
    .y(function(d) { return y(d.temperature); });

var svg = d3.select("#graph-here").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

function getLineData() {
    var oldvals = d3.select("#graph-here").selectAll(".city").remove();
    var oldvals2 = d3.select("#graph-here").selectAll(".axis").remove();
    var oldvals2 = d3.select("#graph-here").selectAll(".legend").remove();

    var contestId = document.getElementById("contest-id").value;
    var questionId = document.getElementById("question-id").value;
    var lineIndex = document.getElementById("line-index").value;
    var searchTake = document.getElementById("search-take").value;
    var searchSkip = document.getElementById("search-skip").value;

    d3.json("/pi/ajax/analyze/lines/" + contestId + "/" + questionId + "/" + lineIndex + "/" + searchTake + "/" + searchSkip, function(error, data) {
    if (error) throw error;

      color.domain(d3.keys(data[0]).filter(function(key) { return key !== "date"; }));

      data.forEach(function(d) {
        d.date = parseDate(d.date);
      });

      var cities = color.domain().map(function(name) {
        return {
          name: name,
          values: data.map(function(d) {
            return {date: d.date, temperature: +d[name]};
          })
        };
      });

      x.domain(d3.extent(data, function(d) { return d.date; }));

      y.domain([
        d3.min(cities, function(c) { return d3.min(c.values, function(v) { return v.temperature; }); }),
        d3.max(cities, function(c) { return d3.max(c.values, function(v) { return v.temperature; }); })
      ]);

      svg.append("g")
          .attr("class", "x axis")
          .attr("transform", "translate(0," + height + ")")
          .call(xAxis);

      svg.append("g")
          .attr("class", "y axis")
          .call(yAxis)
        .append("text")
          .attr("transform", "rotate(-90)")
          .attr("y", 6)
          .attr("dy", ".71em")
          .style("text-anchor", "end")
          .text("Net Offers ($)");

      var city = svg.selectAll(".city")
          .data(cities)
        .enter().append("g")
          .attr("class", "city");

      city.append("path")
          .attr("class", "line")
          .attr("d", function(d) { return line(d.values); })
          .style("stroke", function(d) { return color(d.name); });

      // city.append("text")
      //     .datum(function(d) { return {name: d.name, value: d.values[d.values.length - 1]}; })
      //     .attr("transform", function(d) { return "translate(" + x(d.value.date) + "," + y(d.value.temperature) + ")"; })
      //     .attr("x", 3)
      //     .attr("dy", ".35em")
      //     .text(function(d) { return d.name; });

      var legend = svg.selectAll(".legend")
          .data(color.domain().slice().reverse())
        .enter().append("g")
          .attr("class", "legend")
          .attr("transform", function(d, i) { return "translate(0," + i * 20 + ")"; });

      legend.append("rect")
          .attr("x", width - 678)
          .attr("y", 210)
          .attr("width", 18)
          .attr("height", 18)
          .style("fill", color);

      legend.append("text")
          .attr("x", width - 684)
          .attr("y", 219)
          .attr("dy", ".35em")
          .style("text-anchor", "end")
          .text(function(d) { return d; });
    });
}

</script>

@stop
