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
            !$this->installAdminController() OR
            !$this->registerHook('DisplayBackOfficeHeader')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() OR
            !$this->uninstallAdminController()
        ) {
            return false;
        }

        return true;
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

        if ($id_tab)
        {
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

    public function hookDisplayBackOfficeHeader($params)
    {
        if ("AdminOrders" === Tools::getValue('controller')){
            $this->smarty->assignGlobal("employeeIdProfile", $this->context->employee->id_profile);
            $this->context->controller->addCSS($this->_path . 'views/css/cdgestionpaiements.css');
            $this->context->controller->addJS($this->_path .  'views/js/cdgestionpaiements.js');
        }
    }

}
