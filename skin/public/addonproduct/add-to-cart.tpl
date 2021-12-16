{if is_array($addonproduct) && !empty($addonproduct)}
    <div class="form-group">
        <div class="radio">
            {foreach $addonproduct as $item}
                <label for="param[addonproduct][value][{$item.id}]">
                    <input type="radio" name="param[addonproduct][value]" id="param[addonproduct][value][{$item.id}]" value="{$item.id}"> {$item.name} - {$item.price} €
                </label>
            {/foreach}
        </div>
        <textarea name="param[addonproduct][content_adp]"></textarea>
        <textarea name="param[addonproduct][infos_adp]"></textarea>
    </div>
{/if}