<div class="modal fade gts_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="" method="post" class="gts-form">
          <input type="hidden" name="id" value="{$id}"/>
          <input type="hidden" name="hash" value="{$hash}"/>
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel"></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="{'gettables_close' | lexicon}">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-content">
          <div class="form-group">
          <label class="control-label" for="package">Сгенерировать правила для пакета</label>
              <div class="controls">
                <input type="text" id="package" name="package" class="form-control">
              </div>
          </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{'gettables_close' | lexicon}</button>
            <button type="submit" name="gts_action" value="gtsapi/generate_rules" class="btn btn-primary">Сгенирировать</button>
          </div>
      </form>
    </div>
  </div>
</div>