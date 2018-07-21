{**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 *}

<!-- MODULE homecategoriez -->
{if $categories}
{literal}
    <STYLE TYPE="text/css">
    <!--
        #center_column {
            {/literal}
            {* The width must be equal to "Block module width adjust" option from the module settings. *}
            width: {$block_width}px;
            {literal}
        }
    -->
    </STYLE>
{/literal}
<br class="clear" />
<div id="homecategoriez" class="block products_block homecategoriez-block homecategoriez-categories" style="width:{$block_width}px;">
    <h4>{l s='Popular categories' mod='homecategoriez'}</h4>
        <div class="block_content" style="width:{$block_width}px">
            <ul>
            {foreach from=$categories item=category name=homeCategory}
                {assign var='categoryLink' value=$link->getcategoryLink($category->id_category, $category->link_rewrite)}
                <li style="width:{$block_li_width}px" class="ajax_block_product {if $smarty.foreach.homeCategory.first}first_item{elseif $smarty.foreach.homeCategory.last}last_item{else}item{/if} {if $smarty.foreach.homeCategory.iteration%$nb_items_per_line == 0}last_item_of_line{elseif $smarty.foreach.homeCategory.iteration%$nb_items_per_line == 1}clear{/if} {if $smarty.foreach.homeCategory.iteration > ($smarty.foreach.homeCategory.total - ($smarty.foreach.homeCategory.total % $nb_items_per_line))}last_line{/if}">
                    <a
                        href="{$categoryLink}"
                        title="{$category->name|escape:html:'UTF-8'}"
                        class="product_image"
                        style="
                            background-image: url({$link->getCatImageLink($category->link_rewrite, $category->id_category, $pic_size_type)});
                            background-position: center center;
                            background-repeat: no-repeat;
                            background-size: {$pic_size.width}px {$pic_size.height}px;
                            width: {$block_li_width-4}px;
                        "
                    >
                    </a>
                    <h5><a href="{$categoryLink}" title="{$category->name|escape:html:'UTF-8'}">{$category->name|truncate:42:'...'|escape:'htmlall':'UTF-8'}</a></h5>
                </li>
            {/foreach}
            </ul>
        </div>
</div>
{else}
    {l s='No categories' mod='homecategoriez'}
{/if}
<!-- /MODULE homecategoriez -->