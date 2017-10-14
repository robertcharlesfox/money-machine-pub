  <script
      type="text/javascript"
      src="{{ asset('js/custom/elections.js') }}"></script>
  <script
      type="text/javascript"
      src="{{ asset('js/ea/general_functions.js') }}"></script>
  <script type="text/javascript">
      var GlobalVariables = {
          csrfToken           : <?php echo json_encode($ajax_token); ?>
      };
      $(document).ready(function() {
          Elections.initialize(true);
      });
  </script>

  <script type="text/javascript" src="{{ asset('assets/js/frontend/faq.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/mixitup/src/jquery.mixitup.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/js/jquery.dataTables.min.js') }}" ></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/TableTools/js/dataTables.tableTools.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/Scroller/js/dataTables.scroller.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/RowReorder/js/dataTables.rowReorder.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/Responsive/js/dataTables.responsive.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/vendors/datatables/extensions/bootstrap/dataTables.bootstrap.min.js') }}" ></script>
  <script type="text/javascript" src="{{ asset('assets/js/pages/table-advanced.js') }}" ></script>

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
    .y(function(d) { return y(d.dem_lead); });

var svg = d3.select("#graph-here").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

function getLineData(stat) {
    var oldvals = d3.select("#graph-here").selectAll(".city").remove();
    var oldvals2 = d3.select("#graph-here").selectAll(".axis").remove();
    var oldvals2 = d3.select("#graph-here").selectAll(".legend").remove();

    var raceId = document.getElementById("graph-race-id").value;

    d3.json("/elections/ajax/graph/" + stat + "/" + raceId, function(error, data) {
    if (error) throw error;

      color.domain(d3.keys(data[0]).filter(function(key) { return key !== "date"; }));

      data.forEach(function(d) {
        d.date = parseDate(d.date);
      });

      var increments = color.domain().map(function(name) {
        return {
          name: name,
          values: data.map(function(d) {
            return {date: d.date, dem_lead: +d[name]};
          })
        };
      });

      x.domain(d3.extent(data, function(d) { return d.date; }));

      y.domain([
        d3.min(increments, function(c) { return d3.min(c.values, function(v) { return v.dem_lead; }); }),
        d3.max(increments, function(c) { return d3.max(c.values, function(v) { return v.dem_lead; }); })
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
          .text("Lead for Dem");

      var city = svg.selectAll(".city")
          .data(increments)
        .enter().append("g")
          .attr("class", "city");

      city.append("path")
          .attr("class", "line")
          .attr("d", function(d) { return line(d.values); })
          .style("stroke", function(d) { return color(d.name); });

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

function getPriceQuote(raceId) {
    $('#price-data').html('');
    if (raceId == 0) {
      raceId = document.getElementById("graph-race-id").value;
    };
    $('#price-data-' + raceId).html('');

    d3.json("/elections/ajax/quote/" + raceId, function(error, data) {
    if (error) throw error;
      ///////////////////////////////////////////////////////////////
      console.log('Get Price Quotes JSON Response:', data);
      ///////////////////////////////////////////////////////////////

      data.forEach(function(contract) {
          $('#price-data').html($('#price-data').html() + "<br>" + contract.summary);
          $('#price-data-' + raceId).html($('#price-data-' + raceId).html() + "<br>" + contract.summary);
      });
    });

}

function getVisitMarket(raceId) {
    if (raceId == 0) {
      raceId = document.getElementById("graph-race-id").value;
    };

    d3.json("/elections/ajax/visit/" + raceId, function(error, data) {
    if (error) throw error;
      console.log('Visit Market JSON Response:', data);
    });
}
</script>
