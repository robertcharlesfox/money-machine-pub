@extends('m.base')

@section('css')
<style>
  text {
    font: 10px sans-serif;
  }
</style>
@stop

@section('content')
<div class="container">
  <h3>PredictIt Market Bubbles</h3>
  <div class="well col-md-12">
    <div class="row">
        <div class="col-md-3" id="select-contest">
            <label for="pi_contest_id" class="control-label">Contest:</label>
            <select name="pi_contest_id" id="pi_contest_id" class="form-control">
                <option value="" />
                @foreach ($bubble_contests as $contest)
                    <option value="{{ $contest->id }}" class="option-contest" data-contest-id="{{ $contest->id }}">{{ $contest->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 hidden" id="select-question">
            <label for="pi_question_id" class="control-label">Question:</label>
            <select name="pi_question_id" id="pi_question_id" class="form-control">
                <option value="" />
                @foreach ($bubble_questions as $question)
                    <option value="{{ $question['id'] }}" class="option-question" data-question-contest-id="{{ $question['pi_contest_id'] }}">{{ $question['question_ticker'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" id="button-submit" class="btn btn-success disabled">Submit</button>
        </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js"></script>
<script type="text/javascript">
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

  // Returns a flattened hierarchy containing all leaf nodes under the root.
  function offers(root) {
    var offers = [];
    function recurse(name, node) {
      if (node.children) node.children.forEach(function(child) { recurse(node.name, child); });
      else offers.push({rank: name, timestamp: node.timestamp, action: node.action, price: node.price, value: node.shares});
    }
    recurse(null, root);
    return {children: offers};
  }

  d3.select(self.frameElement).style("height", diameter + "px");

  $('#button-submit').on('click', function() {
    var question_id = $('#pi_question_id').val();
    var oldvals = d3.select("body").selectAll("g").remove();

    d3.json("/onering/ajax/analyze/" + question_id, function(error, root) {
      if (error) throw error;

      var node = svg.selectAll(".node")
          .data(bubble.nodes(offers(root))
          .filter(function(d) { return !d.children; }))
        .enter().append("g")
          .attr("class", "node")
          .attr("transform", function(d) { return "translate(" + d.rank * 50 + "," + ((100 - d.price) * 10) + ")"; });

      node.append("title")
          .text(function(d) { return d.price + ": " + format(d.value) + ": " + d.timestamp; });

      node.append("circle")
          .attr("r", function(d) { return d.r; })
          .style("fill", function(d) { return d.action == "buyYes" ? "#B22222" : d.action == "sellYes" ? "#7FFF00" : "#00FFFF"; });

      node.append("text")
          .attr("dy", ".3em")
          .style("text-anchor", "middle")
          .text(function(d) { return d.price.substring(0, d.r / 3); });
    });
  });

  $('#pi_contest_id').change(function () {
    $('#select-question').removeClass('hidden');
    $('.option-question').show();
    $(".option-question[data-question-contest-id!='" + $('#pi_contest_id').val() + "']").hide();
  });
  $('#pi_question_id').change(function () {
    $('#button-submit').removeClass('disabled');
  });
</script>
@stop
