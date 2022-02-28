{if is_array($addonproduct) && !empty($addonproduct)}
    <div class="form-group">
        <div class="radio has-optional-fields">
            <label for="adp_none">
                <input type="radio" class="optional-field" name="param[addonproduct][value]" id="adp_none" value="0" checked> Ne rien ajouter
            </label>
            {foreach $addonproduct as $item}
                <label for="param[addonproduct][value][{$item.id}]" data-price-additionnal="true">
                    <input type="radio" class="optional-field" name="param[addonproduct][value]" id="param[addonproduct][value][{$item.id}]" data-target="#adp-content" value="{$item.id}"{if $item.price} data-price="{$item.price}" data-vat="21"{/if}> {$item.name} - {if $setting.price_display.value === 'tinc'}{math equation="price * (1 + (vat / 100))" price=$item.price vat=21 format="%.2f"}{else}{$item.price|string_format:"%.2f"}{/if} € {if $setting.price_display.value === 'tinc'}{#tax_included#}{else}{#tax_excluded#}{/if}
                </label>
            {/foreach}
        </div>
    </div>
    <div id="adp-content" class="additional-fields collapse">
        <div class="form-group">
            <label for="adp_c">{#content_adp#}</label>
            <textarea id="adp_c" name="param[addonproduct][content_adp]" rows="5" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label for="adp_i">{#infos_adp#}</label>
            <textarea id="adp_i" name="param[addonproduct][infos_adp]" rows="5" class="form-control"></textarea>
        </div>
    </div>
{/if}