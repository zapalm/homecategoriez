<?php
/**
 * Home Categories Block: module for PrestaShop.
 *
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2012 Maksim T.
 * @link      https://prestashop.modulez.ru/en/frontend-features/31-block-of-categories-on-the-homepage.html The module's homepage
 * @license   https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (false === defined('_PS_VERSION_')) {
    exit;
}

/**
 * Module HomeCategoriez.
 *
 * @author Maksim T. <zapalm@yandex.com>
 */
class HomeCategoriez extends Module
{
    /** The product ID of the module on its homepage. */
    const HOMEPAGE_PRODUCT_ID = 31;

    /** @var bool Is smarty vars already assigned. */
    private static $vars_assigned = false;

    /** @var array Default settings. */
    private $conf_default = [
        'HOMECATEGORIEZ_CATALOG'      => '',
        'HOMECATEGORIEZ_IMAGE_TYPE'   => '',
        'HOMECATEGORIEZ_LIMIT'        => '6',
        'HOMECATEGORIEZ_SHUFFLE'      => '0',
        'HOMECATEGORIEZ_COLS'         => '4',
        'HOMECATEGORIEZ_WIDTH_ADJUST' => '538',
    ];

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function __construct()
    {
        $this->name          = 'homecategoriez';
        $this->tab           = version_compare(_PS_VERSION_, '1.4', '>=') ? 'front_office_features' : 'Tools';
        $this->version       = '1.12.0';
        $this->author        = 'zapalm';
        $this->need_instance = false;
        $this->bootstrap     = false;

        parent::__construct();

        $this->displayName = $this->l('Categories on the homepage');
        $this->description = $this->l('Displays categories in the middle of your homepage');

        $this->renewConfiguration();
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function install()
    {
        $result = parent::install();

        if ($result) {
            foreach ($this->conf_default as $c => $v) {
                Configuration::updateValue($c, $v);
            }

            $result &= $this->registerHook('header');
            if (version_compare(_PS_VERSION_, '1.6', '<') || version_compare(_PS_VERSION_, '1.7', '>=')) {
                $result &= $this->registerHook('home');
            } else {
                $result &= $this->registerHook('displayHomeTab');
                $result &= $this->registerHook('displayHomeTabContent');
            }
        }

        $this->registerModuleOnQualityService('installation');

        return (bool)$result;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function uninstall()
    {
        $result = (bool)parent::uninstall();

        if ($result) {
            foreach ($this->conf_default as $c => $v) {
                Configuration::deleteByName($c);
            }
        }

        $this->registerModuleOnQualityService('uninstallation');

        return $result;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function getContent()
    {
        global $cookie;

        $output = (version_compare(_PS_VERSION_, '1.6', '>=') ? '' : '<h2>' . $this->displayName . '</h2>');

        if (Tools::isSubmit('submit_save')) {
            $res = 1;
            foreach ($this->conf_default as $k => $v) {
                $res &= Configuration::updateValue($k, Tools::getValue($k));
            }

            $output .= $res ? $this->displayConfirmation($this->l('Settings updated')) : $this->displayError($this->l('Some setting not updated'));
        }

        $conf       = Configuration::getMultiple(array_keys($this->conf_default));
        $categories = Category::getHomeCategories($cookie->id_lang, false);
        $root_cat   = Category::getRootCategory($cookie->id_lang);
        $imageTypes = ImageType::getImagesTypes('categories', true);

        $output     .= '
            <form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
                <fieldset>
                    <legend><img src="' . $this->_path . 'logo.png" width="15" height="16" alt="" />' . $this->l('Settings') . '</legend>
                    <label>' . $this->l('Root category of children categories to display') . '</label>
                    <div class="margin-form">
                        <select name="HOMECATEGORIEZ_CATALOG">
                            <option value="' . $root_cat->id . '"' . ($conf['HOMECATEGORIEZ_CATALOG'] == $root_cat->id ? ' selected="selected"' : '') . '>' . $root_cat->name . '</option>';
                            foreach ($categories as $v) {
                                $output .= '<option value="' . $v['id_category'] . '"' . ($conf['HOMECATEGORIEZ_CATALOG'] == $v['id_category'] ? ' selected="selected"' : '') . '>' . $v['name'] . '</option>';
                            }
                            $output .= '
                        </select>
                        <p class="clear">' . $this->l('Choose a root category (default : Home category).') . '</p>
                    </div>
                    <label>' . $this->l('Image size') . '</label>
                    <div class="margin-form">
                        <select name="HOMECATEGORIEZ_IMAGE_TYPE">';
                            foreach ($imageTypes as $v) {
                                $output .= '<option value="' . $v['name'] . '"' . ($conf['HOMECATEGORIEZ_IMAGE_TYPE'] === $v['name'] ? ' selected="selected"' : '') . '>' . $v['name'] . '</option>';
                            }
                            $output .= '
                        </select>
                        <p class="clear">' . $this->l('See the configuration in "Design / Image settings" for PS 1.7 or in "Preferences / Images" for older versions.') . '</p>
                    </div>
                    <label>' . $this->l('The number of categories to display') . '</label>
                    <div class="margin-form">
                        <input type="text" size="1" name="HOMECATEGORIEZ_LIMIT" value="' . $conf['HOMECATEGORIEZ_LIMIT'] . '" />
                        <p class="clear">' . $this->l('The number of categories to display on homepage.')  . ' ' . sprintf($this->l('Default: %s.'), $this->conf_default['HOMECATEGORIEZ_LIMIT']) . '</p>
                    </div>
                    <label>' . $this->l('Shuffle categories') . '</label>
                    <div class="margin-form">
                        <input type="checkbox" name="HOMECATEGORIEZ_SHUFFLE" value="1" ' . ($conf['HOMECATEGORIEZ_SHUFFLE'] ? 'checked="checked"' : '') . '>
                        <p class="clear">' . $this->l('Check it if you want categories to be shuffled.') . '</p>
                    </div>
                    <label><sup style="color: red;">*</sup> ' . $this->l('The number of columns to display') . '</label>
                    <div class="margin-form">
                        <input type="text" size="1" name="HOMECATEGORIEZ_COLS" value="' . $conf['HOMECATEGORIEZ_COLS'] . '" />
                        <p class="clear">' . $this->l('The number of columns to display on homepage.') . ' ' . sprintf($this->l('Default: %s.'), $this->conf_default['HOMECATEGORIEZ_COLS']) . '</p>
                    </div>
                    <label><sup style="color: red;">*</sup> ' . $this->l('Width adjust for the block of categories') . '</label>
                    <div class="margin-form">
                        <input type="text" size="3" name="HOMECATEGORIEZ_WIDTH_ADJUST" value="' . $conf['HOMECATEGORIEZ_WIDTH_ADJUST'] . '" /> px.
                        <p class="clear">' . $this->l('Input a number of pixels to adjust width of the block of categories.') . ' ' . sprintf($this->l('Default: %s.'), $this->conf_default['HOMECATEGORIEZ_WIDTH_ADJUST']) . '</p>
                    </div>
                    <label><span style="color: red;">*</span> &mdash; ' . $this->l('These options are only for PrestaShop 1.5, 1.4 and 1.3.') . '</label>
                    <br class="clear" />
                    <div class="margin-form">
                        <input type="submit" name="submit_save" value="' . $this->l('Save') . '" class="button" />
                    </div>
                </fieldset>
            </form>
        ';

        // The block about the module (version: 2021-08-19)
        $modulezUrl    = 'https://prestashop.modulez.ru' . (Language::getIsoById(false === empty($GLOBALS['cookie']->id_lang) ? $GLOBALS['cookie']->id_lang : Context::getContext()->language->id) === 'ru' ? '/ru/' : '/en/');
        $modulePage    = $modulezUrl . self::HOMEPAGE_PRODUCT_ID . '-' . $this->name . '.html';
        $licenseTitle  = 'Academic Free License (AFL 3.0)';
        $output       .=
            (version_compare(_PS_VERSION_, '1.6', '<') ? '<br class="clear" />' : '') . '
            <div class="panel">
                <div class="panel-heading">
                    <img src="' . $this->_path . 'logo.png" width="16" height="16" alt=""/>
                    ' . $this->l('Module info') . '
                </div>
                <div class="form-wrapper">
                    <div class="row">
                        <div class="form-group col-lg-4" style="display: block; clear: none !important; float: left; width: 33.3%;">
                            <span><b>' . $this->l('Version') . ':</b> ' . $this->version . '</span><br/>
                            <span><b>' . $this->l('License') . ':</b> ' . $licenseTitle . '</span><br/>
                            <span><b>' . $this->l('Website') . ':</b> <a class="link" href="' . $modulePage . '" target="_blank">prestashop.modulez.ru</a></span><br/>
                            <span><b>' . $this->l('Author') . ':</b> ' . $this->author . '</span><br/><br/>
                        </div>
                        <div class="form-group col-lg-2" style="display: block; clear: none !important; float: left; width: 16.6%;">
                            <img width="250" alt="' . $this->l('Website') . '" src="https://prestashop.modulez.ru/img/marketplace-logo.png" />
                        </div>
                    </div>
                </div>
            </div> ' .
            (version_compare(_PS_VERSION_, '1.6', '<') ? '<br class="clear" />' : '') . '
        ';

        return $output;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $cssFile = '1.7.css';
        } elseif (version_compare(_PS_VERSION_, '1.6', '>=')) {
            $cssFile = '1.6.css';
        } else {
            $cssFile = '1.3-1.5.css';
        }

        return '<link href="' . $this->_path . 'views/css/' . $cssFile . '" rel="stylesheet">';
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookHome($params)
    {
        $this->assignCommonVariables($params);

        $templateName = version_compare(_PS_VERSION_, '1.7', '>=')
            ? 'homecategoriez-boilerplate.tpl'
            : 'homecategoriez.tpl'
        ;

        return $this->display(__FILE__, 'views/templates/' . $templateName);
    }

    /**
     * Assign common variables.
     *
     * @param array $params Hook params.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function assignCommonVariables($params)
    {
        global $smarty, $link;

        if (self::$vars_assigned) {
            return;
        }

        $idLanguage = (int)$params['cookie']->id_lang;
        $categories = Category::getChildren((int)Configuration::get('HOMECATEGORIEZ_CATALOG'), $idLanguage, true);
        foreach ($categories as $i => $category) {
            $categories[$i] = new Category($category['id_category'], $idLanguage);
        }

        $rootCategory = new Category((int)Configuration::get('HOMECATEGORIEZ_CATALOG'), $idLanguage);

        if (Configuration::get('HOMECATEGORIEZ_SHUFFLE')) {
            shuffle($categories);
        }

        $limit = (int)Configuration::get('HOMECATEGORIEZ_LIMIT');
        if ($limit > 0) {
            $categories = array_splice($categories, 0, $limit);
        }

        $conf                = Configuration::getMultiple(array_keys($this->conf_default));
        $block_width         = (int)$conf['HOMECATEGORIEZ_WIDTH_ADJUST'];
        $nb_items_per_line   = (int)$conf['HOMECATEGORIEZ_COLS'];
        $pic_size_type       = $conf['HOMECATEGORIEZ_IMAGE_TYPE'];
        $block_width_adjust  = ceil($nb_items_per_line * 2) + 2;
        $block_content_width = $block_width - $block_width_adjust;
        $block_li_width      = ceil($block_content_width / $nb_items_per_line);

        $smarty->assign([
            'rootCategory'      => $rootCategory,
            'categories'        => $categories,
            'link'              => $link,
            'block_width'       => $block_width,
            'nb_items_per_line' => $nb_items_per_line,
            'block_li_width'    => $block_li_width,
            'pic_size_type'     => $pic_size_type,
            'pic_size'          => Image::getSize($pic_size_type),
        ]);

        self::$vars_assigned = true;
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookDisplayHomeTabContent($params)
    {
        $this->assignCommonVariables($params);

        return $this->display(__FILE__, 'views/templates/homecategoriez-bootstrap.tpl');
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookDisplayHomeTab($params)
    {
        return $this->display(__FILE__, 'views/templates/homecategoriez-bootstrap-tab.tpl');
    }

    /**
     * Updates the module configuration.
     *
     * The advantage of the method is not to make a migration script in a case when just added a new option, which has a default value or not.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function renewConfiguration()
    {
        if ($this->active) {
            foreach (array_keys($this->conf_default) as $settingName) {
                $settingValue = (string)Configuration::get($settingName);
                if ('' === $settingValue) {
                    if ('HOMECATEGORIEZ_CATALOG' === $settingName) {
                        $settingValue = (Configuration::get('PS_HOME_CATEGORY') ? Configuration::get('PS_HOME_CATEGORY') : 1);
                        Configuration::updateValue($settingName, $settingValue);
                    } elseif ('HOMECATEGORIEZ_IMAGE_TYPE' === $settingName) {
                        $imageTypes = ImageType::getImagesTypes('categories', true);
                        $imageType  = array_pop($imageTypes);
                        if (is_array($imageType)) {
                            Configuration::updateValue($settingName, $imageType['name']);
                        }
                    } else {
                        Configuration::updateValue($settingName, $this->conf_default[$settingName]);
                    }
                }
            }
        }
    }

    /**
     * Registers current module installation/uninstallation in the quality service.
     *
     * This method is needed for a developer to quickly find out about a problem with installing or uninstalling a module.
     *
     * @param string $operation The operation. Possible values: installation, uninstallation.
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    private function registerModuleOnQualityService($operation)
    {
        @file_get_contents('https://prestashop.modulez.ru/scripts/quality-service/index.php?' . http_build_query([
            'data' => json_encode([
                'productId'           => self::HOMEPAGE_PRODUCT_ID,
                'productSymbolicName' => $this->name,
                'productVersion'      => $this->version,
                'operation'           => $operation,
                'status'              => (empty($this->_errors) ? 'success' : 'error'),
                'message'             => (false === empty($this->_errors) ? strip_tags(stripslashes(implode(' ', (array)$this->_errors))) : ''),
                'prestashopVersion'   => _PS_VERSION_,
                'thirtybeesVersion'   => (defined('_TB_VERSION_') ? _TB_VERSION_ : ''),
                'shopDomain'          => (method_exists('Tools', 'getShopDomain') && Tools::getShopDomain() ? Tools::getShopDomain() : (Configuration::get('PS_SHOP_DOMAIN') ? Configuration::get('PS_SHOP_DOMAIN') : Tools::getHttpHost())),
                'shopEmail'           => Configuration::get('PS_SHOP_EMAIL'), // This public e-mail from a shop's contacts can be used by a developer to send only an urgent information about security issue of a module!
                'phpVersion'          => PHP_VERSION,
                'ioncubeVersion'      => (function_exists('ioncube_loader_iversion') ? ioncube_loader_iversion() : ''),
                'languageIsoCode'     => Language::getIsoById(false === empty($GLOBALS['cookie']->id_lang) ? $GLOBALS['cookie']->id_lang : Context::getContext()->language->id),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]));
    }
}