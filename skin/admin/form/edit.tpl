<div class="row">
    <form id="edit_addonproduct" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=edit&edit={$page.id_vat}" method="post" class="validate_form edit_form col-ph-12 col-lg-8">
        {include file="language/brick/dropdown-lang.tpl"}
        <div class="row">
            <div class="col-ph-12 col-md-3">
                <div class="form-group">
                    <label for="price_adp">{#price#|ucfirst}</label>
                    <input type="text" id="price_adp" name="addonData[price_adp]" class="form-control" value="{$page.price_adp}" placeholder="{#ph_price#|ucfirst}" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-ph-12 col-md-6">
                <div class="tab-content">
                    {foreach $langs as $id => $iso}
                        <fieldset role="tabpanel" class="tab-pane{if $iso@first} active{/if}" id="lang-{$id}">
                            <div class="row">
                                <div class="col-ph-12 col-md-6 col-lg-6">
                                    <div class="form-group">
                                        <label for="content[{$id}][name_adp]">{#name_p#|ucfirst} :</label>
                                        <input type="text" class="form-control" id="content[{$id}][name_adp]" name="content[{$id}][name_adp]" value="{$page.content[{$id}].name_adp}" size="50" />
                                    </div>
                                </div>
                            </div>
                        </fieldset>
                    {/foreach}
                </div>
            </div>
        </div>
        <input type="hidden" id="id_adp" name="id" value="{$page.id_adp}">
        <button class="btn btn-main-theme pull-right" type="submit" name="action" value="edit">{#save#|ucfirst}</button>
    </form>
</div>