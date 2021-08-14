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

$vendorAutoloader = __DIR__ . '/vendor/autoload.php';
if (false === file_exists($vendorAutoloader)) {
    $vendorAutoloader = __DIR__ . '/../../vendor/autoload.php';
}
/** @noinspection PhpIncludeInspection */
require_once $vendorAutoloader;
