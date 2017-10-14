@extends('josh/layouts/nobottom')

{{-- Page title --}}
@section('title')
AutoTrade Dashboard
@parent
@stop

{{-- page level styles --}}
@section('header_styles')
@stop

{{-- Page content --}}
@section('content')
    <!-- Container Section Start -->
    <div class="container">
        <!--Content Section Start -->
        <h2></h2>
        <div style="height:350px;">
            <form class="form-horizontal" id="addcontest" action="/autotrade/contest/add" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="POST">
            <fieldset>

            <!-- Form Name -->
            <legend>Enter a new AutoTrade contest</legend>

            <!-- Select Basic -->
            <div class="form-group">
              <label class="col-md-2 control-label" for="category">Select Category</label>
              <div class="col-md-4">
                <select id="category" name="category" class="form-control">
                  <option value=""></option>
                  <option value="obama">RCP Obama Approval</option>
                  <option value="doc">RCP Direction of Country</option>
                  <option value="congress">RCP Congressional Approval</option>
                  <option value="polls_clinton_vs_trump">RCP Clinton Vs Trump - National</option>
                  <option value="polls_clinton_vs_trump_pa">RCP Clinton Vs Trump - PA</option>
                  <option value="polls_clinton_vs_trump_fl">RCP Clinton Vs Trump - FL</option>
                  <option value="polls_clinton_vs_trump_oh">RCP Clinton Vs Trump - OH</option>
                  <option value="polls_clinton_vs_trump_nc">RCP Clinton Vs Trump - NC</option>
                  <option value="polls_clinton_vs_trump_nv">RCP Clinton Vs Trump - NV</option>
                  <option value="Johnson">RCP Johnson</option>
                  <option value="Stein">RCP Stein</option>
                  <option value="clinton_fav">RCP Clinton Fav</option>
                  <option value="trump_fav">RCP Trump Fav</option>
                  <option value="states_clinton_vs_trump">States - Clinton Vs Trump</option>
                  <option value="fundraising">Fundraising</option>
                  <option value="debates">Debates</option>
                  <option value="binary">Binary Event</option>
                </select>
              </div>
            </div>

            <!-- Select Basic -->
            <div class="form-group select-fundraising">
              <label class="col-md-2 control-label" for="committee">Committee</label>
              <div class="col-md-3">
                <select id="committee" name="committee" class="form-control">
                  <option value=""></option>
                  @foreach($fundraising_committees as $committee)
                    <option value="{{ $committee }}">{{ $committee }}</option>
                  @endforeach
                </select>
              </div>
              <label class="col-md-1 control-label" for="month">Month</label>
              <div class="col-md-2">
                <select id="month" name="month" class="form-control">
                  <option value=""></option>
                  <option value="August">July (August Monthly)</option>
                  <option value="September">August (September Monthly)</option>
                  <option value="October">September (October Monthly)</option>
                  <option value="November">October (November Monthly)</option>
                  <option value="December">November (December Monthly)</option>
                </select>
              </div>
              <label class="col-md-1 control-label" for="details">Details</label>
              <div class="col-md-2">
                <select id="details" name="details" class="form-control">
                  <option value=""></option>
                  <option value="6c">Line 6c</option>
                  <option value="7">Line 7</option>
                  <option value="9">Line 9</option>
                </select>
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-2 control-label" for="name">Name</label>  
              <div class="col-md-4">
              <input id="name" name="name" type="text" class="form-control input-md">
              </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
              <label class="col-md-2 control-label" for="url_of_answer">URL of Marketplace</label>  
              <div class="col-md-4">
              <input id="url_of_answer" name="url_of_answer" type="text" class="form-control input-md">
              </div>
            </div>

            <!-- Button -->
            <div class="form-group">
              <div class="col-md-4 col-md-offset-7">
                <button type="submit" class="btn btn-success">Save</button>
              </div>
            </div>

            </fieldset>
            </form>

        </div>
        <!-- //Content Section End -->
    </div>
    
@stop

{{-- page level scripts --}}
@section('footer_scripts')

@stop
