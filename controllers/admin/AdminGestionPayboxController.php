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
 * @author  Dominique <dominique@chez-dominique.fr>
 * @copyright   2007-2016 Chez-dominique
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once __DIR__ . "/../../classes/models/OrderGestionPayment.php";
require_once __DIR__ . "/../../classes/managers/OrderGestionPaymentManager.php";
require_once __DIR__ . "/../../classes/models/OrderGestionEcheancier.php";
require_once __DIR__ . "/../../classes/managers/OrderGestionEcheancierManager.php";
require_once __DIR__ . "/../../classes/models/OrderGestionPaymentPaybox.php";

class AdminGestionPayboxController extends ModuleAdminController
{
    protected $html;
    protected $smarty;
    protected $path_tpl;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->lang = false;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdgestionpaiements/views/templates/admin/importpaybox/';

        parent::__construct();
    }

    public function initContent()
    {
        $this->smarty->assign(array(
            "test" => $this->path_tpl
        ));
        $this->html .= $this->smarty->fetch($this->path_tpl . 'importCsv.tpl');
        $this->content = $this->html;

        parent::initContent();
    }

    public function setMedia()
    {
        $this->addCSS($this->path_tpl . '../../../css/uploadfile.css');
        $this->addCSS($this->path_tpl . '../../../css/uploadfile.custom.css');
        $this->addJquery("1.9.1");
        $this->addJS($this->path_tpl . '../../../js/jquery.uploadfile.js');
        $this->addJS($this->path_tpl . '../../../js/cdgestionpaybox.js');
        parent::setMedia();
    }

    public function ajaxProcessUploadCsv()
    {
        $ret = $this->uploadFile();
        die($ret);
    }

    private function uploadFile()
    {
        $output_dir = _PS_UPLOAD_DIR_;
        if (isset($_FILES["csvPaybox"])) {
            $ret = array();
            //	This is for custom errors;
            /*	$custom_error= array();
                $custom_error['jquery-upload-file-error']="File already exists";
                echo json_encode($custom_error);
                die();
            */
            $error = $_FILES["csvPaybox"]["error"];
            //You need to handle  both cases
            //If Any browser does not support serializing of multiple files using FormData()
            if (!is_array($_FILES["csvPaybox"]["name"])) //single file
            {
                $fileName = $_FILES["csvPaybox"]["name"];
                move_uploaded_file($_FILES["csvPaybox"]["tmp_name"], $output_dir . $fileName);
                $ret[] = $fileName;
            } else  //Multiple files, file[]
            {
                $fileCount = count($_FILES["csvPaybox"]["name"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = $_FILES["csvPaybox"]["name"][$i];
                    move_uploaded_file($_FILES["csvPaybox"]["tmp_name"][$i], $output_dir . $fileName);
                    $ret[] = $fileName;
                }
            }
            return json_encode($ret);
        }
    }
}