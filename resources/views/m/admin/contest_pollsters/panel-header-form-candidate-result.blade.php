<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#resultModal-{{ $pollster->id }}">
  Result
</button>

<div class="modal fade" id="resultModal-{{ $pollster->id }}" tabindex="-1" role="dialog" aria-labelledby="resultModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="contest-pollster-{{ $pollster->id }}" action="/admin/contest_pollsters/result" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="_method" value="POST">
        <input type="hidden" name="contest_pollster_id" value="{{ $pollster->id }}">

        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title">Enter Poll Result for {{ $pollster->name }}</h4>
        </div>

        <div class="modal-body">
          @if ($contest->id == 12)
          <div class="form-group row">
              <div class="col-md-4">
                <label for="early_Clinton" class="control-label">Clinton</label>
                <input type="number" name="early_Clinton" value="{{ $pollster->early_Clinton }}" class="form-control" />
              </div>
              <div class="col-md-4">
                <label for="early_Sanders" class="control-label">Sanders</label>
                <input type="number" name="early_Sanders" value="{{ $pollster->early_Sanders }}" class="form-control" />
              </div>
              <div class="col-md-4">
                <label for="early_OMalley" class="control-label">O'Malley</label>
                <input type="number" name="early_OMalley" value="{{ $pollster->early_OMalley }}" class="form-control" />
              </div>
          </div>
          @else
          <div class="form-group row">
              <div class="col-md-3">
                <label for="early_Trump" class="control-label">Trump</label>
                <input type="number" name="early_Trump" value="{{ $pollster->early_Trump }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Carson" class="control-label">Carson</label>
                <input type="number" name="early_Carson" value="{{ $pollster->early_Carson }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Rubio" class="control-label">Rubio</label>
                <input type="number" name="early_Rubio" value="{{ $pollster->early_Rubio }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Cruz" class="control-label">Cruz</label>
                <input type="number" name="early_Cruz" value="{{ $pollster->early_Cruz }}" class="form-control" />
              </div>
          </div>
          <div class="form-group row">
              <div class="col-md-3">
                <label for="early_Bush" class="control-label">Bush</label>
                <input type="number" name="early_Bush" value="{{ $pollster->early_Bush }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Fiorina" class="control-label">Fiorina</label>
                <input type="number" name="early_Fiorina" value="{{ $pollster->early_Fiorina }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Paul" class="control-label">Paul</label>
                <input type="number" name="early_Paul" value="{{ $pollster->early_Paul }}" class="form-control" />
              </div>
              <div class="col-md-3">
                <label for="early_Christie" class="control-label">Christie</label>
                <input type="number" name="early_Christie" value="{{ $pollster->early_Christie }}" class="form-control" />
              </div>
          </div>
          @endif
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>