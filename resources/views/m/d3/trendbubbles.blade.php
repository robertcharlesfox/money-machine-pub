@extends('m.base')

@section('css')
<style>
text {
  font: 10px sans-serif;
}
</style>
@stop

@section('content')

<button id="load-button" onclick="updateValues(-1)" class="btn btn-success">Load Previous Market</button>
<button id="load-button" onclick="updateValues(1)" class="btn btn-success">Load Next Market</button>

@stop

@section('scripts')

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
<script>

var scrape = 5;

var diameter = 960,
    format = d3.format(",d"),
    color = d3.scale.category20c();

var bubble = d3.layout.pack()
    .sort(null)
    .size([(diameter/3), diameter])
    .padding(1.5);

var svg = d3.select("body").append("svg")
    .attr("width", diameter)
    .attr("height", diameter)
    .attr("class", "bubble");

function updateValues(x) {
  var oldvals = d3.select("body").selectAll("g").remove();

  scrape = scrape + x;

  d3.json("/pi/ajax/analyze/" + scrape, function(error, root) {
    if (error) throw error;

    var node = svg.selectAll(".node")
        .data(bubble.nodes(offers(root))
        .filter(function(d) { return !d.children; }))
      .enter().append("g")
        .attr("class", "node")
        .attr("transform", function(d) { return "translate(" + d.rank * 50 + "," + ((100 - d.price) * 10) + ")"; });

    node.append("title")
        .text(function(d) { return d.price + ": " + format(d.value); });

    node.append("circle")
        .attr("r", function(d) { return d.r; })
        .style("fill", function(d) { return d.action == "buyYes" ? "#B22222" : d.action == "sellYes" ? "#7FFF00" : "#00FFFF"; });

    node.append("text")
        .attr("dy", ".3em")
        .style("text-anchor", "middle")
        .text(function(d) { return d.price.substring(0, d.r / 3); });
  });
}

// Returns a flattened hierarchy containing all leaf nodes under the root.
function offers(root) {
  var offers = [];

  function recurse(name, node) {
    if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
    else offers.push({rank: name, action: node.action, price: node.price, value: node.shares});
  }

  recurse(null, root);
  return {children: offers};
}

d3.select(self.frameElement).style("height", diameter + "px");

</script>

@stop
