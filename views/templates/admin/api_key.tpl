{extends file='controllers/layout.tpl'}

{block name="page_header_toolbar_btn"}
<div class="page-bar toolbarBox">
    <div class="btn-toolbar">
        {if $api_key}
        <form method="post">
            <input type="hidden" name="delete_api_key" value="1">
            <button type="submit" class="btn btn-default">
                <i class="icon-trash"></i> {$smarty.const._DELETE_}
            </button>
        </form>
        {else}
        <form method="post">
            <input type="hidden" name="generate_api_key" value="1">
            <button type="submit" class="btn btn-default">
                <i class="icon-refresh"></i> {$smarty.const._CREATE_}
            </button>
        </form>
        {/if}
    </div>
</div>
{/block}

{block name="content"}
    <h1>{$smarty.const._API_KEY_}</h1>
    {if $api_key}
        <p><strong>Your API Key:</strong> {$api_key}</p>
    {else}
        <p>No API key found.</p>
    {/if}
{/block}
