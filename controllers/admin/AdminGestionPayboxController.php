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
require_once __DIR__ . "/../../classes/managers/OrderGestionPaymentPayboxManager.php";

class AdminGestionPayboxController extends ModuleAdminController
{
    protected $html;
    protected $smarty;
    protected $path_tpl;
    protected $headerCsv = "RemittancePaybox;Bank;Site;Rank;ShopName;IdPaybox;Date;TransactionId;IdAppel;DateOfIssue;HourOfIssue";

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
        $this->addJS($this->path_tpl . '../../../js/jquery.form.js');
        $this->addJS($this->path_tpl . '../../../js/jquery.uploadfile.js');
        $this->addJS($this->path_tpl . '../../../js/cdgestionpaybox.js');
        parent::setMedia();
    }

    public function ajaxProcessUploadCsv()
    {
        // Upload du fichier envoyé dans le répertoire /upload/
        $uploadFile = $this->uploadFile();

        // Décompression du fichier, et récupération dans une variable
        $fileName = false;
        if ($uploadFile['fileUploaded']) {
            $fileName = $this->unzipFile($uploadFile);
        }

        if ($fileName['fileUploaded']) {
            $fileContent = $this->importCsvFromFile($fileName);
        }


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
                $ret['fileUploaded'] = move_uploaded_file($_FILES["csvPaybox"]["tmp_name"], $output_dir . $fileName);
                $ret['fileName'] = $fileName;
            } else  //Multiple files, file[]
            {
                $fileCount = count($_FILES["csvPaybox"]["name"]);
                for ($i = 0; $i < $fileCount; $i++) {
                    $fileName = $_FILES["csvPaybox"]["name"][$i];
                    $ret['fileUploaded'] = move_uploaded_file($_FILES["csvPaybox"]["tmp_name"][$i], $output_dir . $fileName);
                    $ret['fileName'] = $fileName;
                }
            }
            return $ret;
        }
        return false;
    }

    private function unzipFile($uploadFile)
    {
        $fileName = false;
        $zipArchive = new ZipArchive();
        $zip = new ZipArchive;
        $openFile = $zip->open(_PS_UPLOAD_DIR_ . $uploadFile['fileName']);
        if ($openFile === TRUE) {
            $zip->extractTo(_PS_UPLOAD_DIR_);
            $fileName = trim($zip->getNameIndex(0), '/');
            $zip->close();
        }

        return $fileName;
    }

    private function importCsvFromFile($fileName)
    {
        // Test de la bonne extension et du debut du fichier csv
        $error = false;
        $headerFile = '';
        if (substr($fileName, -3, 3) != "csv") {
            $error = true;
        }
        if (!$error) {
            $headerFile = file_get_contents(_PS_UPLOAD_DIR_ . $fileName, null, null, 0, 100);
            if ($headerFile !== $this->headerCsv) {
                $error = true;
            }
        }

        // importe le fichier dans un array
        if (!$error) {
            $contentFile = file_get_contents(_PS_UPLOAD_DIR_ . $fileName);
            $lines = explode("\n", $contentFile);
            $arrayCsv = array();
            foreach ($lines as $line) {
                $arrayCsv[] = str_getcsv($line, ';');
            }

            // Import en bdd
//      $row =
//      0 => string 'RemittancePaybox' (length=16)
//      1 => string 'Bank' (length=4)
//      2 => string 'Site' (length=4)
//      3 => string 'Rank' (length=4)
//      4 => string 'ShopName' (length=8)
//      5 => string 'IdPaybox' (length=8)
//      6 => string 'Date' (length=4)
//      7 => string 'TransactionId' (length=13)
//      8 => string 'IdAppel' (length=7)
//      9 => string 'DateOfIssue' (length=11)
//      10 => string 'HourOfIssue' (length=11)
//      11 => string 'DateOfExpiry' (length=12)
//      12 => string 'Reference' (length=9)
//      13 => string 'Origin' (length=6)
//      14 => string 'Type' (length=4)
//      15 => string 'Canal' (length=5)
//      16 => string 'NumberOfAuthorization' (length=21)
//      17 => string 'Amount' (length=6)
//      18 => string 'Currency' (length=8)
//      19 => string 'Entity' (length=6)
//      20 => string 'Operator' (length=8)
//      21 => string 'Country' (length=7)
//      22 => string 'CountryIP' (length=9)
//      23 => string 'Payment' (length=7)
//      24 => string 'ThreeDSecureStatus' (length=18)
//      25 => string 'ThreeDSecureInscription' (length=23)
//      26 => string 'ThreeDSecureWarranted' (length=21)
//      27 => string 'RefArchive' (length=10)
//      28 => string 'Status' (length=6)
//      29 => string 'PAN' (length=3)
//      30 => string 'IP' (length=2)
//      31 => string 'ErrorCode' (length=9)
            array_shift($arrayCsv);
            foreach ($arrayCsv as $row) {
                $error = '';
                $reference = preg_split("/[_-]/", $row[12]);
                $id_order_gestion_payment_paybox = OrderGestionPaymentPayboxManager::isOrderPayboxExistByTransactionId($row[7]);

                if ($id_order_gestion_payment_paybox) {
                    $orderPaybox = new OrderGestionPaymentPaybox($id_order_gestion_payment_paybox);
                } else {
                    $orderPaybox = new OrderGestionPaymentPaybox();
                }

                $orderPaybox->id_order = $reference[0];
                $orderPaybox->transaction_id = $row[7];
                $orderPaybox->date_of_issue = date("Y-m-d");//$row[9];
                $orderPaybox->reference = $row[12];
                $orderPaybox->amount = $row[17];
                $orderPaybox->status = $row[28];

                if ($id_order_gestion_payment_paybox) {
                    $error = $orderPaybox->update();
                } else {
                    $error = $orderPaybox->add();
                }
                var_dump($error);
                // Todo reprendre ici par le formatage de la date et la gestion des erreurs
            }

            die();
        }

    }
}