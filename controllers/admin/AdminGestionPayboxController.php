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

const CDGESTION_DAYS_BETWEEN_ECHEANCE = 2;

require_once __DIR__ . "/../../classes/models/OrderGestionPayment.php";
require_once __DIR__ . "/../../classes/managers/OrderGestionPaymentManager.php";
require_once __DIR__ . "/../../classes/models/OrderGestionEcheancier.php";
require_once __DIR__ . "/../../classes/managers/OrderGestionEcheancierManager.php";
require_once __DIR__ . "/../../classes/OrderGestionPaymentPayboxClass.php";
require_once __DIR__ . "/../../classes/managers/OrderGestionPaymentPayboxManager.php";

class AdminGestionPayboxController extends ModuleAdminController
{
    protected $html;
    protected $smarty;
    protected $path_tpl;
    protected $original_filter;
    protected $headerCsv = "RemittancePaybox;Bank;Site;Rank;ShopName;IdPaybox;Date;TransactionId;IdAppel;DateOfIssue;HourOfIssue";
    private $idOrder = 57857;

    public function __construct()
    {
        $this->table = 'order_gestion_payment_paybox';
        $this->identifier = 'id_order_gestion_payment_paybox';
        $this->_orderBy = 'a!date_of_issue';
        $this->_orderWay = 'DESC';
        $this->original_filter = '';
        $this->list_no_link = true;
        $this->bulk_actions = array(
            'Update' => array(
                'text' => $this->l('Valider les paiements Paybox'),
                'icon' => 'icon-refresh'),
            'Reset' => array(
                'text' => 'Reset order '.$this->idOrder.' pour test module paiement',
                'icon' => 'icon-eye'
            )
        );

        $this->bootstrap = true;
        $this->lang = false;
        $this->context = Context::getContext();
        $this->smarty = $this->context->smarty;
        $this->path_tpl = _PS_MODULE_DIR_ . 'cdgestionpaiements/views/templates/admin/importpaybox/';
        $this->_select = "GROUP_CONCAT(oge.id_order_gestion_echeancier) AS id_payment, oge.payment_amount, oge.payment_date  ";
        $this->_join .= "LEFT JOIN `" . _DB_PREFIX_ . "orders` AS o ON a.id_order = o.id_order ";
        $this->_join .= "LEFT JOIN `" . _DB_PREFIX_ . "order_gestion_payment` AS ogp ON o.id_order = ogp.id_order ";
        $this->_join .= "LEFT JOIN `" . _DB_PREFIX_ . "order_gestion_echeancier` AS oge ON ogp.id_order_gestion_payment = oge.id_order_gestion_payment ";
        $this->_join .= "AND a.date_of_issue < (oge.payment_date + " . CDGESTION_DAYS_BETWEEN_ECHEANCE . ") ";
        $this->_join .= "AND a.date_of_issue > (oge.payment_date - " . CDGESTION_DAYS_BETWEEN_ECHEANCE . ") ";
        $this->_group .= "GROUP BY a.id_order_gestion_payment_paybox ";

        $this->fields_list = array(
            'id_order' => array(
                'title' => 'Id Cmd',
                'filter_key' => 'a!id_order',
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'checked' => array(
                'title' => $this->l('Echéancier'),
                'align' => 'text-center',
                'type' => 'checkbox',
                'class' => 'fixed-width-xs',
                'callback' => 'getOrderGestionEcheancier',
            ),
            'id_order_payment' => array(
                'title' => $this->l('Status'),
                'align' => 'text-center',
                'class' => 'fixed-width-md',
                'callback' => 'statusEcheancier',
            ),
            'amount' => array(
                'title' => '€ Paybox',
                'callback' => 'setAmount',
                'align' => 'text-right',
                'class' => 'fixed-width-xs'
            ),
            'payment_amount' => array(
                'title' => '€ Echéance',
                'callback' => 'setAmount',
                'align' => 'text-right',
                'class' => 'fixed-width-xs'
            ),
            'date_of_issue' => array(
                'title' => 'Date Paybox',
                'callback' => 'formatDateField',
                'align' => 'text-center',
                'class' => 'fixed-width-sm'
            ),
            'payment_date' => array(
                'title' => 'Date Echéance',
                'callback' => 'formatDateField',
                'align' => 'text-center',
                'class' => 'fixed-width-sm'
            ),
            'status' => array(
                'title' => 'Status',
                'align' => 'text-right',
                'class' => 'fixed-width-sm'
            ),
            'transaction_id' => array(
                'title' => 'transaction_id',
                'align' => 'text-right',
                'class' => 'fixed-width-sm'
            ),
            'reference' => array(
                'title' => 'Commande',
                'callback' => 'linkOrder',
                'align' => 'text-center',
                'class' => 'fixed-width-sm'
            ),
        );

        parent::__construct();
    }

    public function initContent()
    {
        $this->smarty->assign(array(
            'test' => $this->path_tpl,
            'employee_profile' => $this->getProfile(),
        ));
        $this->html .= $this->smarty->fetch($this->path_tpl . 'importCsv.tpl');
        $this->content = $this->html;

        parent::initContent();
    }

    private function getProfile()
    {
        $id_profile = $this->context->employee->id_profile;

        return Profile::getProfileAccess($id_profile, (int)Tab::getIdFromClassName('AdminGestionPaybox'));
    }

    public function renderList()
    {
        if (isset($this->_filter) && trim($this->_filter) == '') {
            $this->_filter = $this->original_filter;
        }

        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function renderView()
    {
        $this->tpl_view_vars['order_gestion_payment_paybox'] = $this->loadObject();
        return parent::renderView();
    }

    public function setMedia()
    {
        $this->addCSS($this->path_tpl . '../../../css/uploadfile.css');
        $this->addCSS($this->path_tpl . '../../../css/adminGestionPaybox.css');
        $this->addJquery("1.9.1");
        $this->addJS($this->path_tpl . '../../../js/jquery.form.js');
        $this->addJS($this->path_tpl . '../../../js/jquery.uploadfile.js');
        $this->addJS($this->path_tpl . '../../../js/cdgestionpaybox.js');
        parent::setMedia();
    }

    public function linkOrder($value, $params)
    {
        $linkController =  Context::getContext()->link->getAdminLink('AdminOrders');
        $link = '<a href="'.$linkController.'&id_order='.$params['id_order'].'&vieworder" class="btn btn-primary"><i class="icon-eye"></i>&nbsp;Voir</a>';
        return $link;
    }

    public function statusEcheancier($value, $params)
    {
        $calendar ='';
        $euro ='';
        $class_euro = 'text-danger';
        $class_calendar = 'text-danger';
        if ($params['status'] == 'Télécollecté' && !empty($params['payment_date']) && !empty($params['payment_amount']))  {
            if (!empty($params['date_of_issue']) && !empty($params['payment_date'])) {
                if ($params['date_of_issue'] === $params['payment_date']) {
                    $class_calendar = 'text-success';
                }
            }
            if (!empty($params['payment_amount']) && !empty($params['amount'])) {
                if ($params['payment_amount'] === $params['amount']) {
                    $class_euro = 'text-success';
                }
            }
            $calendar = '<i class="icon-calendar ' . $class_calendar . '" title = "Date"></i>';
            $euro = '<i class="icon-euro ' . $class_euro . '" title = "Montant"></i>';
        }

        return $calendar . '&nbsp;&nbsp;&nbsp;' . $euro;
    }

    public function getOrderGestionEcheancier($value, $payment)
    {
        if ($payment['status'] == 'Télécollecté') {
            if ($payment['checked'] == 1) {
                return "<i class='icon-check text-success'></i>";
            } else {
                $id_order = $payment['id_order'];
                $echeances = OrderGestionEcheancier::getAllEcheancesAVenirByIdOrder($id_order);
                if (count($echeances) > 0) {
                    foreach ($echeances as $echeance) {
                        $pay_status = $this->setPaymentStatus((int)$payment['amount'], (int)$echeance['payment_amount']);
                        $checked = ($pay_status == 'pay_ok') ? 'checked' : '';
                        if ($this->isExistAnEcheance($payment, $echeance) == true) {
                            $div_start = '<label class="cdgestion_checkbox ' . $pay_status . '">';
                            $div_end = '</label>';
                            return $div_start . "<input type='checkbox' name='payment_payboxBox[]' value='" . $payment['id_order_gestion_payment_paybox'] . "-" . $echeance['id_order_gestion_echeancier'] . "' " . $checked . " class='noborder " . $pay_status . "'>" . $div_end;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param $payment
     * @param $echeance
     * @return string
     */
    public function setPaymentStatus($payment, $echeance)
    {
        $pay_status = "";
        if ($echeance == $payment) {
            $pay_status = "pay_ok";
        } else {
            $pay_status = "pay_not_ok";
        }

        return $pay_status;
    }

    /**
     * Si une écheance existe dans le creneau de jours définit, return true
     * @param $payment Payment Paybox
     * @param $echeance Echeances de la commande
     * @return bool
     */
    private function isExistAnEcheance($payment, $echeance)
    {
        $datePaymentPaybox = new DateTime($payment['date_of_issue']);
        $dateEcheanceOrder = new DateTime($echeance['payment_date']);
        $dayBetweenEcheance = $datePaymentPaybox->diff($dateEcheanceOrder);

        if ($echeance['payed'] == 0 && $dayBetweenEcheance->days < CDGESTION_DAYS_BETWEEN_ECHEANCE) {
            return true;
        }

        return false;
    }

    public function postProcess()
    {
        parent::postProcess();
    }

    public function processBulkReset()
    {
        $profil = $this->getProfile();
        if ($profil['delete'] == 1) {
            $sql = "DELETE FROM `ps_order_payment` WHERE order_reference = '".$this->idOrder."'";
            DB::getInstance()->execute($sql);

            $sql = "UPDATE `ps_orders` SET total_paid_real = '120' WHERE id_order = '".$this->idOrder."' ";
            DB::getInstance()->execute($sql);

            $sql = "SELECT id_order_gestion_payment FROM `ps_order_gestion_payment` WHERE id_order = '".$this->idOrder."'";
            $id_gestion_payment = DB::getInstance()->getValue($sql);

            $sql = "DELETE FROM `ps_order_gestion_payment` WHERE id_order_gestion_payment = " . $id_gestion_payment;
            DB::getInstance()->execute($sql);

            $sql = "DELETE FROM `order_gestion_echeancier` WHERE id_order_gestion_payment = " . $id_gestion_payment;
            DB::getInstance()->execute($sql);

            $sql = "UPDATE `ps_order_gestion_payment_paybox` SET checked = 0 WHERE id_order = '".$this->idOrder."'";
            DB::getInstance()->execute($sql);
        } else {
            $this->errors[] = Tools::displayError('Vous n\'avez pas la permission de modifier ce contenu.');
        }
    }

    public function processBulkUpdate()
    {
        if (Tools::isSubmit('payment_payboxBox') && !Tools::isSubmit('submitFilter')) {
            $this->updatePaymentsPaybox(Tools::getValue('payment_payboxBox'));
        }
    }

    private function updatePaymentsPaybox($id_orders)
    {
        foreach ($id_orders as $id_order) {
            $ids_payments = explode('-', $id_order);
            $id_payment_paybox = (int)$ids_payments[0];
            $id_payment_echeancier = (int)$ids_payments[1];
            $paymentPaybox = new OrderGestionPaymentPayboxClass($id_payment_paybox);
            $paymentPaybox->checked = 1;
            $paymentPaybox->id_order_gestion_echeancier = $id_payment_echeancier;
            $paymentPaybox->id_order_payment = $this->setPayment($paymentPaybox);
            $paymentPaybox->update();
        }
    }

    private function setPayment(OrderGestionPaymentPayboxClass $paymentPaybox)
    {
        $payment['id_order_payment'] = null;
        $orderEcheancier = new OrderGestionEcheancier($paymentPaybox->id_order_gestion_echeancier);
        $commande = new Order($paymentPaybox->id_order);
        $invoice = new OrderInvoice($this->getInvoiceId($commande->invoice_number));
        $setPayment = $commande->addOrderPayment(
            $paymentPaybox->amount / 100, "Carte Bancaire", $paymentPaybox->transaction_id, null,
            $paymentPaybox->date_of_issue . ' 00:00:00', $invoice);
        if ($setPayment) {
            $payment = $this->getByOrderIdTransaction($paymentPaybox->transaction_id);
            $orderEcheancier->payment_transaction_id = $payment['id_order_payment'];
            $orderEcheancier->id_employee = $this->context->employee->id;
            $orderEcheancier->payed = 1;
            $orderEcheancier->update();
        }

        return $payment['id_order_payment'];
    }

    public function getInvoiceId($order_number)
    {
        if (!$order_number)
            return false;

        return Db::getInstance()->getValue('
			SELECT `id_order_invoice`
			FROM `' . _DB_PREFIX_ . 'order_invoice`
			WHERE `number` = ' . $order_number
        );
    }

    public function getByOrderIdTransaction($order_reference)
    {
        return Db::getInstance()->getRow('
			SELECT *
			FROM `' . _DB_PREFIX_ . 'order_payment`
			WHERE `transaction_id` = \'' . pSQL($order_reference) . '\'');
    }

    public function setAmount($value)
    {
        return number_format(($value / 100), 2) . ' €';
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
                $error = array();
                $reference = preg_split("/[_-]/", $row[12]);
                $id_order_gestion_payment_paybox = OrderGestionPaymentPayboxManager::isOrderPayboxExistByTransactionId($row[7]);

                if ($id_order_gestion_payment_paybox) {
                    $orderPaybox = new OrderGestionPaymentPayboxClass($id_order_gestion_payment_paybox);
                } else {
                    $orderPaybox = new OrderGestionPaymentPayboxClass();
                }

                $orderPaybox->id_order = (int)$reference[0];
                $orderPaybox->transaction_id = (int)$row[7];
                $orderPaybox->date_of_issue = $this->formatDate($row[9]);
                $orderPaybox->reference = $row[12];
                $orderPaybox->amount = floatval($row[17]);
                $orderPaybox->status = utf8_encode($row[28]);

                if ($id_order_gestion_payment_paybox) {
                    $error[] = $orderPaybox->update();
                } else {
                    $error[] = $orderPaybox->add();
                }
            }
        }

        return $error;
    }

    private function formatDate($stringDate)
    {
        $arrayDate = explode('/', $stringDate);
        $day = $arrayDate[0];
        $month = $arrayDate[1];
        $year = $arrayDate[2];

        return $year . '-' . $month . '-' . $day;
    }

    public function formatDateField($value)
    {
        return date('d-m-Y', strtotime($value));
    }
}