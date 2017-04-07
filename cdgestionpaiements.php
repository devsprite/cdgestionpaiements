<?php
/**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2017 Chez-Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
    exit;

class Cdgestionpaiements extends Module
{

    protected $_errors = array();
    protected $_html = 'Module de gestion des paiements.';

    public function __construct()
    {
        $this->name = 'cdgestionpaiements';
        $this->tab = 'billing_invoicing';
        $this->version = '1.0.0';
        $this->author = 'Dominique';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Gestion des paiements');
        $this->description = $this->l('Ajoute un échéancier de paiements avec calcul semi-automatique dans la commande');
        $this->confirmUninstall = $this->l('Are you sure you want to delete this module?');
    }

    public function install()
    {
        if (!parent::install() OR
            !$this->registerHook('DisplayBackOfficeHeader') OR
            !$this->installAdminController() OR
            !$this->installAdminImportPayboxController() OR
            !$this->createTableOrderGestionPayment() OR
            !$this->createTableOrderGestionEcheance() OR
            !$this->createTableOrderGestionPaymentPaybox()
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() OR
            !$this->uninstallAdminController() OR
            !$this->uninstallAdminImportPayboxController() OR
            !$this->removeTables()
        ) {
            return false;
        }

        return true;
    }

    private function createTableOrderGestionPayment()
    {
        $sql = "CREATE TABLE `" . _DB_PREFIX_ . "order_gestion_payment` (
                `id_order_gestion_payment` INT NOT NULL AUTO_INCREMENT ,
                `id_order` INT NOT NULL ,
                `number_echeance` INT NOT NULL DEFAULT '0',
                `accompte` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
                PRIMARY KEY (`id_order_gestion_payment`)) 
                ENGINE = '" . _MYSQL_ENGINE_ . "' DEFAULT CHARSET=utf8;";
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    private function createTableOrderGestionEcheance()
    {
        $sql = "CREATE TABLE `" . _DB_PREFIX_ . "order_gestion_echeancier` (
                 `id_order_gestion_echeancier` INT NOT NULL AUTO_INCREMENT ,
                 `id_order_gestion_payment` INT NOT NULL ,
                 `payment_date` DATE NOT NULL DEFAULT '0000-00-00' ,
                 `payment_method` VARCHAR(255) NOT NULL ,
                 `payment_transaction_id` INT NOT NULL DEFAULT '0' ,
                 `payment_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00' ,
                 `payed` TINYINT NOT NULL DEFAULT '0' ,
                 `id_employee` INT NOT NULL DEFAULT '0' ,
                 PRIMARY KEY (`id_order_gestion_echeancier`)) 
                 ENGINE = '" . _MYSQL_ENGINE_ . "' DEFAULT CHARSET=utf8;";
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    private function createTableOrderGestionPaymentPaybox()
    {
        $sql = "CREATE TABLE `" . _DB_PREFIX_ . "order_gestion_payment_paybox` (
                `id_order_gestion_payment_paybox` INT NOT NULL AUTO_INCREMENT ,
                `id_order` INT NOT NULL ,
                `id_order_gestion_echeancier` INT NOT NULL DEFAULT '0' ,
                `id_order_payment` INT NOT NULL DEFAULT '0' ,
                `transaction_id` INT NOT NULL DEFAULT '0' ,
                `date_of_issue` DATE NOT NULL DEFAULT '0000-00-00' ,
                `reference` VARCHAR(255) NOT NULL DEFAULT '' ,
                `amount` BIGINT NOT NULL DEFAULT '0' ,
                `status` VARCHAR(255) NOT NULL DEFAULT '' ,
                `checked` TINYINT NOT NULL DEFAULT '0' ,
                PRIMARY KEY (`id_order_gestion_payment_paybox`)) 
                ENGINE = '" . _MYSQL_ENGINE_ . "' DEFAULT CHARSET=utf8;";
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    private function removeTables()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . "order_gestion_payment`,
                `" . _DB_PREFIX_ . "order_gestion_echeancier`,
                `" . _DB_PREFIX_ . "order_gestion_payment_paybox`;";
        if (!DB::getInstance()->execute($sql)) {
            return false;
        }
        return true;
    }

    private function installAdminImportPayboxController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGestionPaybox';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'Import Paybox';
        $tab->id_parent = 136;
        $tab->module = $this->name;

        return (bool)$tab->add();
    }

    private function uninstallAdminImportPayboxController()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminGestionPaybox');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return (bool)$tab->delete();
        }

        return false;
    }

    private function installAdminController()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminGestionPaiements';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang)
            $tab->name[$lang['id_lang']] = 'GestionPaiements';
        $tab->id_parent = -1;
        $tab->module = $this->name;

        return (bool)$tab->add();
    }

    private function uninstallAdminController()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminGestionPaiements');

        if ($id_tab) {
            $tab = new Tab($id_tab);
            return (bool)$tab->delete();
        }

        return false;
    }

    public function getContent()
    {
        $this->_html .= Tools::getValue('controller');
        return $this->_html;
    }

    public function hookDisplayBackOfficeHeader()
    {
        if ("AdminOrders" === Tools::getValue('controller')) {
            $this->context->controller->addCSS($this->_path . 'views/css/cdgestionpaiements.css');
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path . 'views/js/mustache.js');
            $this->context->controller->addJS($this->_path . 'views/js/templatePayment.js');
            $this->context->controller->addJS($this->_path . 'views/js/cdgestionpaiements.js');
        }
    }

}
