{**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE homecategoriez -->
<div id="homecategoriez">
    <h4>{l s='Popular categories' mod='homecategoriez'}</h4>
    <ul class="row">
        {foreach from=$categories item=category name=homeCategory}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            {assign var='imageLink' value=$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)}
            <li class="col-xs-12 col-sm-6 col-lg-2">
                <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                    {if $category->id_image|intval > 0}
                        <img
                            src="{$imageLink}"
                            width="{$pic_size.width}"
                            height="{$pic_size.height}"
                            alt="{$category->name|escape:html:'UTF-8'}"
                            title="{$category->name|escape:html:'UTF-8'}"
                        >
                    {else}
                        <img
                            src="{$urls.img_cat_url|escape:'html':'UTF-8'}{$language.iso_code|escape:'html':'UTF-8'}.jpg"
                            width="{$pic_size.width}"
                            height="{$pic_size.height}"
                            title="{l s='No image' mod='homecategoriez'}"
                        >
                    {/if}
                </a>
                <h5 class="category-title">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {$category->name|escape:html:'UTF-8'}
                    </a>
                </h5>
                <p class="category-description">
                    <a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">
                        {$category->description|strip_tags|stripslashes|escape:html:'UTF-8'}
                    </a>
                </p>
            </li>
        {foreachelse}
            {l s='No categories' mod='homecategoriez'}
        {/foreach}
    </ul>
</div>
<!-- /MODULE homecategoriez -->