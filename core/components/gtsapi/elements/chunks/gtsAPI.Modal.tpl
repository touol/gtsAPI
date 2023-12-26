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
          <label class="control-label" for="rule_json">Экспорт-импорт правила</label>
              <div class="controls">
                <textarea id="rule_json" name="rule_json" class="form-control"
                style="min-height:400px;"
                >{$rule_json}</textarea>
              </div>
          </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{'gettables_close' | lexicon}</button>
            <button type="submit" name="gts_action" value="gtsapi/save_rule" class="btn btn-primary">{'gettables_save' | lexicon}</button>
          </div>
      </form>
    </div>
  </div>
</div>