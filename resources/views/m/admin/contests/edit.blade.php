@extends('m.base')
@section('content')
<h1>{{ isset($contest) ? 'Edit Contest' : 'Add Contest' }}</h1>

<div class="panel panel-default">
    <div class="panel-body">
        <form action="/admin/contests" method="post">
            <div class="col-md-6">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="contest_id" value="{{ isset($contest) ? $contest->id : '' }}">
                <h2>Information</h2>
                <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                    <label for="name" class="control-label">Name:</label>
                    <input type="text" name="name" value="{{ old('name', isset($contest) ? $contest->name : null) }}" class="form-control">
                    {!! $errors->first('name', '<span class="help-block">:message</span>') !!}
                </div>
                <div class="form-group {{ $errors->has('url_of_answer') ? 'has-error' : '' }}">
                    <label for="url_of_answer" class="control-label">URL of Answer:</label>
                    <input type="text" name="url_of_answer" value="{{ old('url_of_answer', isset($contest) ? $contest->url_of_answer : null) }}" class="form-control">
                    {!! $errors->first('url_of_answer', '<span class="help-block">:message</span>') !!}
                </div>
                <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                    <label for="category" class="control-label">Category:</label>
                    <select name="category" id="category" class="form-control">
                        @if (! isset($contest))
                            <option value=""></option>
                        @endif
                        @foreach ($categories as $machine => $human)
                            @if (isset($contest) and $contest->category === $machine)
                                <option selected="selected" value="{{ $machine }}">{{ $human }}</option>
                            @else
                                <option value="{{ $machine }}">{{ $human }}</option>
                            @endif
                        @endforeach
                    </select>
                    {!! $errors->first('category', '<span class="help-block">:message</span>') !!}
                </div>
                <div class="form-group">
                    <label for="rcp_scrape_frequency" class="control-label">RCP Scrape Frequency:</label>
                    <select name="rcp_scrape_frequency" id="rcp_scrape_frequency" class="form-control">
                        @if (! isset($contest))
                            <option value=""></option>
                        @endif
                        @foreach ($scrape_frequencies as $frequency)
                            @if (isset($contest) and $contest->rcp_scrape_frequency == $frequency)
                                <option selected="selected" value="{{ $frequency }}">{{ $frequency }}</option>
                            @else
                                <option value="{{ $frequency }}">{{ $frequency }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="rcp_scrapes_per_minute" class="control-label">RCP Scrapes Per Minute:</label>
                    <select name="rcp_scrapes_per_minute" id="rcp_scrapes_per_minute" class="form-control">
                        @if (! isset($contest))
                            <option value=""></option>
                        @endif
                        @foreach ($scrape_frequencies as $frequency)
                            @if (isset($contest) and $contest->rcp_scrapes_per_minute == $frequency)
                                <option selected="selected" value="{{ $frequency }}">{{ $frequency }}</option>
                            @else
                                <option value="{{ $frequency }}">{{ $frequency }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="rcp_update_txt_alert" class="control-label">RCP Update TXT Alert:</label>
                    @if (isset($contest) && $contest->rcp_update_txt_alert)
                        <input type="checkbox" name="rcp_update_txt_alert" id="rcp_update_txt_alert" checked="checked" value="1" />
                    @else
                        <input type="checkbox" name="rcp_update_txt_alert" id="rcp_update_txt_alert" value="1" />
                    @endif
                </div>
                <div class="form-group">
                    @if (isset($contest))
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <a href="/admin/contests" class="btn btn-default">Done</a>
                    @else
                        <button type="submit" id="button-create" class="btn btn-success">Create</button>
                        <a href="/admin/contests" class="btn btn-default">Cancel</a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
@stop