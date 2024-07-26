<form action="{$form_action}" method="post" class="defaultForm form-horizontal">
    <div class="panel">
        <div class="panel-heading">
            {l s='Add RetJet Request Form identifier' d='Modules.RetJetIntegration.Admin'}
        </div>
        <div class="form-wrapper">
            <div class="form-group">
                <label class="control-label col-lg-3">
                    {l s='Request Form identifier' d='Modules.RetJetIntegration.Admin'}
                </label>
                <div class="col-lg-9">
                    <input type="text" name="RETJET_COMPANY_ID" value="{$company_id|escape:'html':'UTF-8'}" />
                    <p class="help-block">
                        {l s='Take it from RetJet panel: Request form page -> Settings -> Form identifier' d='Modules.RetJetIntegration.Admin'} <br />
                        <strong><a href="{$rj_base_url}/panel/landing_page" target="_blank">{l s='Click here' d='Modules.RetJetIntegration.Admin'}</a></strong>
                        {l s='and go to RetJet panel.' d='Modules.RetJetIntegration.Admin'}
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" name="submitRetJetConfig" class="btn btn-default pull-right">
                {l s='Save' d='Modules.RetJetIntegration.Admin'}
            </button>
        </div>
    </div>
</form>
