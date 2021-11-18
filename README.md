# addonproduct
Plugin addonproduct for Magix CMS 3

Ajouter des éléments supplémentaire au cartpay dans votre site.

## Installation
* Décompresser l'archive dans le dossier "plugins" de magix cms
* Connectez-vous dans l'administration de votre site internet
* Cliquer sur l'onglet plugins du menu déroulant pour sélectionner addonproduct.
* Une fois dans le plugin, laisser faire l'auto installation
* Il ne reste que la configuration du plugin pour correspondre avec vos données.

### Infos
Ce plugin est utilisé pour compléter d'autres plugins comme "cartpay" ou tout autre système ayant besoin d'attributs.

### Exemple d'utilisation a placer dans le fichier add-to-cart.tpl
```smarty
{addonproduct_data}
{if is_array($addonproduct) && !empty($addonproduct)}
<div class="form-group">
    <div class="radio">
        {foreach $addonproduct as $item}
        <label for="param[addonproduct]">
            <input type="radio" name="param[addonproduct]" id="param[addonproduct][{$item.id}]" value="{$item.id}"> {$item.name} - {$item.price} €
        </label>
        {/foreach}
    </div>
    <textarea name="addonContent[content_adp]"></textarea>
    <textarea name="addonContent[infos_adp]"></textarea>
</div>
{/if}
````
