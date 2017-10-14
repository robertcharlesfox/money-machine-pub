@extends('josh/layouts/nobottom')

{{-- Page title --}}
@section('title')
Money Flow Analysis
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
@stop
{{-- breadcrumb --}}
@section('top')
    <div class="breadcum">
        <div class="container">
            <ol class="breadcrumb">
                <li>
                    <a href="dashboard"> <i class="livicon icon3 icon4" data-name="home" data-size="18" data-loop="true" data-c="#3d3d3d" data-hc="#3d3d3d"></i>Dashboard
                    </a>
                </li>
                <li class="hidden-xs">
                    <i class="livicon icon3" data-name="angle-double-right" data-size="18" data-loop="true" data-c="#01bc8c" data-hc="#01bc8c"></i>
                    <a href="#">Money Flow</a>
                </li>
            </ol>
            <div class="pull-right">
                <i class="livicon icon3" data-name="edit" data-size="20" data-loop="true" data-c="#3d3d3d" data-hc="#3d3d3d"></i> Blank Page
            </div>
        </div>
    </div>
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
                                <div class="panel-heading">
                                    <h4 class="panel-title">
                                        <a class="collapsed" data-toggle="collapse" data-parent="#faq" href="#markets">
                                            <strong class="c-gray-light">Recent Markets:</strong>
                                        </a>
                                    </h4>
                                </div>
                                <div id="markets" class="panel-collapse collapse">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="panel panel-primary filterable">
                                                <div class="panel-body table-responsive">
                                                    <table class="table table-striped table-bordered" id="table1">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>New Shrs</th>
                                                                <th>Swap Shrs</th>
                                                                <th>YES ¢</th>
                                                                <th>NO ¢</th>
                                                                <th>Spread ¢</th>
                                                                <th>Chg. YES ¢/$</th>
                                                                <th>Chg. NO ¢/$</th>
                                                                <th>Chg. Net $</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($markets as $market)
                                                            <tr>
                                                                <th>
      <button id="load-button-{{ $market['scrape']->id }}" onclick="updateValues({{ $market['scrape']->id }})" class="btn btn-success">
        {{ $market['scrape']->created_at }}
      </button>
                                                                </th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'new_shares')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'swapped_shares')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'market_support_yes_side_price')) }} / 
                                                                    {{ array_sum(array_pluck($market['markets'], 'market_support_yes_side_dollars')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'market_support_no_side_price')) }} / 
                                                                    {{ array_sum(array_pluck($market['markets'], 'market_support_no_side_dollars')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'market_support_net_price_spread')) / count($market['markets']) }} / 
                                                                    {{ array_sum(array_pluck($market['markets'], 'market_support_net_dollars')) / count($market['markets']) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'change_yes_price')) }} / 
                                                                    {{ array_sum(array_pluck($market['markets'], 'change_yes_dollars')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'change_no_price')) }} / 
                                                                    {{ array_sum(array_pluck($market['markets'], 'change_no_dollars')) }}</th>
                                                                <th>{{ array_sum(array_pluck($market['markets'], 'change_net_dollars')) }}</th>
                                                            </tr>
                                                            
                                                            @foreach ($market['markets'] as $question_id => $q_market)
                                                            @if (isset($q_market['new_shares']))
                                                            <tr>
                                                                <td>{{ $question_id }}</td>
                                                                <td>{{ $q_market['new_shares'] }}</td>
                                                                <td>{{ $q_market['swapped_shares'] }}</td>
                                                                <td>{{ $q_market['market_support_yes_side_price'] }} / 
                                                                    {{ $q_market['market_support_yes_side_dollars'] }}</td>
                                                                <td>{{ $q_market['market_support_no_side_price'] }} / 
                                                                    {{ $q_market['market_support_no_side_dollars'] }}</td>
                                                                <td>{{ $q_market['market_support_net_price_spread'] }} / 
                                                                    {{ $q_market['market_support_net_dollars'] }}</td>
                                                                <td>{{ $q_market['change_yes_price'] }} / 
                                                                    {{ $q_market['change_yes_dollars'] }}</td>
                                                                <td>{{ $q_market['change_no_price'] }} / 
                                                                    {{ $q_market['change_no_dollars'] }}</td>
                                                                <td>{{ $q_market['change_net_dollars'] }}</td>
                                                            </tr>
                                                            @endif
                                                            @endforeach
                                                            
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
<script>

var diameter = 960,
    format = d3.format(",d"),
    color = d3.scale.category20c();

var bubble = d3.layout.pack()
    .sort(null)
    .size([(diameter/3), diameter])
    .padding(1.5);

var svg = d3.select("#graph-here").append("svg")
    .attr("width", diameter)
    .attr("height", diameter)
    .attr("class", "bubble");

function updateValues(scrape_id) {
  var oldvals = d3.select("#graph-here").selectAll("g").remove();

  d3.json("/pi/ajax/analyze/moflow/" + scrape_id, function(error, root) {
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
