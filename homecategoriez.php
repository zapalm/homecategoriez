<?php
/**
 * Home Categories Block: module for PrestaShop 1.3-1.6
 *
 * @author zapalm <zapalm@ya.ru>
 * @copyright (c) 2012-2015, zapalm
 * @link http://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_'))
	exit;

class HomeCategoriez extends Module
{
	/** @var bool Assign smarty vars only ones */
	private static $vars_assigned = false;

	/** @var array Default settings */
	private $conf_default = array(
		'HOMECATEGORIEZ_CATALOG' => 1,
		'HOMECATEGORIEZ_COLS' => 4,
		'HOMECATEGORIEZ_WIDTH_ADJUST' => 538,
	);

	public function __construct()
	{
		$this->name = 'homecategoriez';
		$this->tab = version_compare(_PS_VERSION_, '1.4', '>=') ? 'front_office_features' : 'Tools';
		$this->version = '1.2.0';
		$this->author = 'zapalm';
		$this->need_instance = 0;
		$this->ps_versions_compliancy = array('min' => '1.3.0.0', 'max' => '1.6.1.0');
		$this->bootstrap = false;

		parent::__construct();

		$this->displayName = $this->l('Categories on the homepage');
		$this->description = $this->l('Displays categories in the middle of your homepage');
	}

	public function install()
	{
		foreach ($this->conf_default as $c => $v)
			Configuration::updateValue($c, $v);

		$res = parent::install();

		if (version_compare(_PS_VERSION_, '1.6', '<'))
			$res &= $this->registerHook('home');
		else
		{
			$res &= $this->registerHook('displayHomeTab');
			$res &= $this->registerHook('displayHomeTabContent');
		}

		return $res;
	}

	public function uninstall()
	{
		foreach ($this->conf_default as $c => $v)
			Configuration::deleteByName($c);

		return parent::uninstall();
	}

	public function getContent()
	{
		global $cookie;

		$output = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submit_save'))
		{
			$res = 1;
			foreach ($this->conf_default as $k => $v)
				$res &= Configuration::updateValue($k, (int)Tools::getValue($k));

			$output .= $res ? $this->displayConfirmation($this->l('Settings updated')) : $this->displayError($this->l('Some setting not updated'));
		}

		$conf = Configuration::getMultiple(array_keys($this->conf_default));
		$categories = Category::getHomeCategories($cookie->id_lang, false);
		$root_cat = Category::getRootCategory($cookie->id_lang);
		$output .= '
			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
					<legend><img src="'._PS_ADMIN_IMG_.'cog.gif" />'.$this->l('Settings').'</legend>
					<label>'.$this->l('Root category of children categories to display').'</label>
					<div class="margin-form">
						<select name="HOMECATEGORIEZ_CATALOG">
							<option value="'.$root_cat->id.'"'.($conf['HOMECATEGORIEZ_CATALOG'] == $root_cat->id ? ' selected="selected"' : '').'>'.$root_cat->name.'</option>';
							foreach ($categories as $v)
								$output .= '<option value="'.$v['id_category'].'"'.($conf['HOMECATEGORIEZ_CATALOG'] == $v['id_category'] ? ' selected="selected"' : '').'>'.$v['name'].'</option>';
							$output .= '
						</select>
						<p class="clear">'.$this->l('Choose a root category (default : Home category).').'</p>
					</div>
					<label>'.$this->l('Number of columns to display').'<sup>*</sup></label>
					<div class="margin-form">
						<input type="text" size="1" name="HOMECATEGORIEZ_COLS" value="'.($conf['HOMECATEGORIEZ_COLS'] ? $conf['HOMECATEGORIEZ_COLS'] : '4').'" />
						<p class="clear">'.$this->l('A number of columns to display on homepage (default: 4).').'</p>
					</div>
					<label>'.$this->l('Width adjust for the block of categories').'<sup>*</sup></label>
					<div class="margin-form">
						<input type="text" size="3" name="HOMECATEGORIEZ_WIDTH_ADJUST" value="'.($conf['HOMECATEGORIEZ_WIDTH_ADJUST'] ? $conf['HOMECATEGORIEZ_WIDTH_ADJUST'] : '0').'" /> px.
						<p class="clear">'.$this->l('Input a number of pixels to adjust width of the block of categories.').'</p>
					</div>
					<label>'.$this->l('* Only for PrestaShop less then 1.6.').'</label>
					<br class="clear" />
					<div class="margin-form">
						<input type="submit" name="submit_save" value="'.$this->l('Save').'" class="button" />
					</div>
				</fieldset>
			</form>
			<br class="clear" />
		';

		return $output;
	}

	public function hookHome($params)
	{
		global $smarty;

		$this->assignCommonVars($params);
		$conf = Configuration::getMultiple(array_keys($this->conf_default));
		$block_width = (int)$conf['HOMECATEGORIEZ_WIDTH_ADJUST'];
		$nb_items_per_line = (int)$conf['HOMECATEGORIEZ_COLS'];
		$block_width_adjust = ceil($nb_items_per_line * 2) + 2;
		$block_content_width = $block_width - $block_width_adjust;
		$block_li_width = ceil($block_content_width / $nb_items_per_line);
		$pic_size_type = 'home';

		$smarty->assign(array(
			'block_width' => $block_width,
			'nb_items_per_line' => $nb_items_per_line,
			'block_li_width' => $block_li_width,
			'pic_size_type' => $pic_size_type,
			'pic_size' => Image::getSize($pic_size_type),
		));

		return $this->display(__FILE__, 'homecategoriez.tpl');
	}

	private function assignCommonVars($params)
	{
		global $smarty, $link;

		if (self::$vars_assigned)
			return;

		$categories = Category::getChildren((int)Configuration::get('HOMECATEGORIEZ_CATALOG'), (int)$params['cookie']->id_lang, true);

		$smarty->assign(array(
			'categories' => $categories,
			'link' => $link,
		));

		self::$vars_assigned = true;
	}

	public function hookDisplayHomeTabContent($params)
	{
		$this->assignCommonVars($params);
		$pic_size_type = 'category_default';

		$this->smarty->assign(array(
			'pic_size_type' => $pic_size_type,
		));

		return $this->display(__FILE__, 'homecategoriez-bootstrap.tpl');
	}

	public function hookDisplayHomeTab($params)
	{
		return $this->display(__FILE__, 'homecategoriez-bootstrap-tab.tpl');
	}
}