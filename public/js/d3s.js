// Graph of Mailchimp campaign member counts
var memberCounts = new Array();
$("div.campaign-member-count").each(function(){
  memberCounts.push($(this).data("value"));
});
var x = d3.scale.linear()
    .domain([0, d3.max(memberCounts)])
    .range([0, 200]);
d3.select("#member-count")
  .selectAll("div")
    .data(memberCounts)
  .enter().append("div")
    .style("width", function(d) { return x(d) + "px"; })
    .text(function(d) { return d; });


// Graph of Mailchimp campaign open rates
var openRates = new Array();
$("div.campaign-open-rate").each(function(){
  openRates.push($(this).data("value"));
});
var x = d3.scale.linear()
    .domain([0, d3.max(openRates)])
    .range([0, 200]);
d3.select("#open-rate")
  .selectAll("div")
    .data(openRates)
  .enter().append("div")
    .style("width", function(d) { return x(d) + "px"; })
    .text(function(d) { return d; });


// Graph of Mailchimp campaign click rates
var clickRates = new Array();
$("div.campaign-click-rate").each(function(){
  clickRates.push($(this).data("value"));
});
var x = d3.scale.linear()
    .domain([0, d3.max(clickRates)])
    .range([0, 200]);
d3.select("#click-rate")
  .selectAll("div")
    .data(clickRates)
  .enter().append("div")
    .style("width", function(d) { return x(d) + "px"; })
    .text(function(d) { return d; });
