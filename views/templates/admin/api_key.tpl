{if isset($confirmations) && $confirmations}
    <div class="alert alert-success">
        {foreach from=$confirmations item=msg}
            <p>{$msg}</p>
        {/foreach}
    </div>
{/if}

{if isset($errors) && $errors}
    <div class="alert alert-danger">
        {foreach from=$errors item=msg}
            <p>{$msg}</p>
        {/foreach}
    </div>
{/if}

<div class="panel" id="fieldset_0">
    <div class="panel-heading">
        <i class="icon-cogs"></i>{l s='API Key Management' d='Modules.RetJetIntegration.Admin'}
    </div>

    <div class="form-wrapper">
        <div class="form-group">
        {if $api_key}
            <label class="control-label col-lg-4 text-right">
                {l s='Your API Key:' d='Modules.RetJetIntegration.Admin'}
            </label>

            <div class="col-lg-8">
                <input type="text" value="{$api_key}" diabled="disabled" />

                <p class="help-block">
                     {l s='API Key uset for RetJet integration.' d='Modules.RetJetIntegration.Admin'}
                </p>
            </div>


            <label class="control-label col-lg-4 text-right">
                {l s='Integrate Your Store with RetJet:' d='Modules.RetJetIntegration.Admin'}
            </label>

            <div class="col-lg-8">
                <a href="{$integration_url}" target="_blank">
                    <button type="button" class="btn btn-primary">
                        <i class="icon-link"></i> {l s='Start integration' d='Modules.RetJetIntegration.Admin'}
                    </button>
                </a>

                <p class="help-block">
                     {l s='Redirects to the RetJet panel and creates a Sales Channel.' d='Modules.RetJetIntegration.Admin'}
                </p>
            </div>
        {else}
            <label class="control-label col-lg-4">
                {l s='No API key found.' d='Modules.RetJetIntegration.Admin'}
            </label>

            <div class="col-lg-8">
                <form method="post" action="{$form_action}">
                    <input type="hidden" name="generate_api_key" value="1">
                    <button type="submit" class="btn btn-default">
                        <i class="icon-refresh"></i> {l s='Generate' d='Admin.Actions'}
                    </button>
                </form>

                <p class="help-block">
                     {l s='Generate API Key to use in setup integration.' d='Modules.RetJetIntegration.Admin'}
                </p>
            </div>

        {/if}
           </div>

    </div><!-- /.form-wrapper -->

    <div class="panel-footer">

            {if $api_key}
                <form id="delete-api-key-form" method="post" action="{$form_action}" onsubmit="return confirm('Are you sure you want to delete the API key?');">
                    <input type="hidden" name="delete_api_key" value="1">
                    <button type="submit" class="btn btn-default pull-right"">
                        <i class="icon-trash"></i> {l s='Delete' d='Admin.Actions'}
                    </button>
                </form>
            {/if}

    </div>

</div>
