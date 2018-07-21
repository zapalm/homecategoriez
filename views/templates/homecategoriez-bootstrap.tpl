{**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE homecategoriez -->
{if isset($categories) && $categories}
    {* define number of categories per line for different devices *}
    {* @todo: add to the module's settings *}
    {* @todo: rename vars for the norms *}
    {assign var='nbItemsPerLine' value=4}
    {assign var='nbItemsPerLineTablet' value=3}
    {assign var='nbItemsPerLineMobile' value=2}
    
    {assign var='nbLi' value=$categories|@count}
    {math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
    {math equation="nbLi/nbItemsPerLineTablet" nbLi=$nbLi nbItemsPerLineTablet=$nbItemsPerLineTablet assign=nbLinesTablet}
    
    <ul id="homecategoriez" class="product_list grid row homecategoriez tab-pane">
        {foreach from=$categories item=category name=homeCategory}
            {math equation="(total%perLine)" total=$smarty.foreach.homeCategory.total perLine=$nbItemsPerLine assign=total_items}
            {math equation="(total%perLineT)" total=$smarty.foreach.homeCategory.total perLineT=$nbItemsPerLineTablet assign=total_items_tablet}
            {math equation="(total%perLineM)" total=$smarty.foreach.homeCategory.total perLineM=$nbItemsPerLineMobile assign=total_items_mobile}
            {if $total_items == 0}{assign var='total_items' value=$nbItemsPerLine}{/if}
            {if $total_items_tablet == 0}{assign var='total_items_tablet' value=$nbItemsPerLineTablet}{/if}
            {if $total_items_mobile == 0}{assign var='total_items_mobile' value=$nbItemsPerLineMobile}{/if}
            {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
            <li class="ajax_block_product col-xs-12 col-sm-4 col-md-3 {if $smarty.foreach.homeCategory.iteration%$nbItemsPerLine == 0}last-in-line{elseif $smarty.foreach.homeCategory.iteration%$nbItemsPerLine == 1}first-in-line{/if} {if $smarty.foreach.homeCategory.iteration > ($smarty.foreach.homeCategory.total - $total_items)}last-line{/if} {if $smarty.foreach.homeCategory.iteration%$nbItemsPerLineTablet == 0}last-item-of-tablet-line{elseif $smarty.foreach.homeCategory.iteration%$nbItemsPerLineTablet == 1}first-item-of-tablet-line{/if} {if $smarty.foreach.homeCategory.iteration%$nbItemsPerLineMobile == 0}last-item-of-mobile-line{elseif $smarty.foreach.homeCategory.iteration%$nbItemsPerLineMobile == 1}first-item-of-mobile-line{/if} {if $smarty.foreach.homeCategory.iteration > ($smarty.foreach.homeCategory.total - $total_items_mobile)}last-mobile-line{/if}">
                <div class="product-container">
                    <div class="left-block">
                        <div class="product-image-container">
                            <a class="product_img_link" href="{$categoryLink|escape:'html':'UTF-8'}" title="{$category->name|escape:'html':'UTF-8'}">
                                <img class="replace-2x img-responsive" src="{$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)|escape:'html':'UTF-8'}" alt="{$category->name|escape:'html':'UTF-8'}" {if isset($homeSize)} width="{$homeSize.width}" height="{$homeSize.height}"{/if} />
                            </a>
                        </div>
                    </div>
                    <div class="right-block">
                        <h5>
                            <a class="product-name" href="{$categoryLink|escape:'html':'UTF-8'}" title="{$category->name|escape:'htmlall':'UTF-8'}">
                                {$category->name|truncate:42:'...'|escape:'htmlall':'UTF-8'}
                            </a>
                        </h5>
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{else}
    <ul id="homecategoriez" class="homecategoriez tab-pane">
        <li class="alert alert-info">{l s='No categories' mod='homecategoriez'}</li>
    </ul>
{/if}
<!-- /MODULE homecategoriez -->