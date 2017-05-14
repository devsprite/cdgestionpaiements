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

class AdminGestionPaiementsController extends ModuleAdminController
{
    const CDGESTION_ACCOMPTE_MINI = 20;
    const CDGESTION_ACCOMPTE_POURCENTAGE_MINI = 0.1;
    const CDGESTION_NUMBER_ECHEANCE_DEFAULT = 4;
    const CDGESTION_PAYMENT_METHOD = array(
        array('paymentMethod' => 'Carte Bancaire'),
        array('paymentMethod' => 'Chèque'),
        array('paymentMethod' => 'Virement Bancaire'),
        array('paymentMethod' => 'PayPal'),
        array('paymentMethod' => 'Gestion des abonnements'),
    );

    private $orderInformations = array();

    public function ajaxProcessGetProfil()
    {
        $profil = $this->getProfile();

        die(Tools::jsonEncode($profil));
    }

    public function ajaxProcessGetOrderInformations()
    {
        $this->orderInformations['id_order'] = (int)Tools::getValue("id_order");
        $this->getOrderInformations();

        die(Tools::jsonEncode($this->orderInformations));
    }

    private function getOrderInformations()
    {
        $order = new Order($this->orderInformations['id_order']);
        $orderGestionPaymentManager = new OrderGestionPaymentManager();
        $orderGestionEcheancierManager = new OrderGestionEcheancierManager();

        $this->orderInformations['id_customer'] = (int)$order->id_customer;
        $this->orderInformations['profil'] = $this->getProfile();
        $this->orderInformations['total_paid_real'] = round($order->total_paid_real, 2);
        $this->orderInformations['orders_total_paid_tax_incl'] = round($order->total_paid_tax_incl, 2);
        $this->orderInformations['order_reste_a_payer'] = $this->getResteAPayer($order);
        $this->orderInformations['paymentsNumber'] = $this->getPaymentsNumber($order);
        $this->orderInformations['accompte'] = (float)$orderGestionPaymentManager->getAccompteByOrder($this->orderInformations['id_order']);
        $this->orderInformations['accompteMini'] = self::CDGESTION_ACCOMPTE_MINI;
        $this->orderInformations['numberEcheancesTotal'] = (int)$orderGestionPaymentManager->getNumberEcheancesTotalByOrder($this->orderInformations['id_order']);
        $this->orderInformations['numberEcheancesPayed'] = (int)$orderGestionPaymentManager->getNumberEcheancesPayed($this->orderInformations['id_order']);
        $this->orderInformations['numberEcheancesAVenir'] = (int)$orderGestionEcheancierManager->getNumberEcheancesAVenir($this->orderInformations['id_order']);
        $this->orderInformations['numberEcheancesMini'] = 1;//(int)$orderGestionPaymentManager->getNumberEcheancesMini($this->orderInformations['id_order']);
        $this->orderInformations['numberEcheancesMax'] = (int)$orderGestionPaymentManager->getNumberEcheancesMax($this->orderInformations['id_order']);
        $this->orderInformations['echeancier'] = $orderGestionPaymentManager->getEcheancier($this->orderInformations);
    }

    /**
     * Update Accompte value
     */
    public function ajaxProcessUpdateAccompte()
    {
        $accompte = (float)Tools::getValue("accompte");
        $numberEcheances = $this->nombreEcheance((int)Tools::getValue("number_echeance"));
        $id_order = (int)Tools::getValue("id_order");

        $orderGestionPaymentManager = new OrderGestionPaymentManager();
        $orderGestionPaymentManager->updateEcheance($id_order, $numberEcheances);


        if (empty($id_order)) {
            die(Tools::jsonEncode(array("message" => "id_order is empty", "error" => true)));
        }

        $order = new Order($id_order);
        $orderResteAPayer = $order->total_paid_tax_incl - $order->total_paid_real;

        if (($accompte != 0) && (($accompte < self::CDGESTION_ACCOMPTE_MINI) || ($accompte > $orderResteAPayer))) {
            die(Tools::jsonEncode(array("message" => $accompte . "L'accompte doit être compris entre " . self::CDGESTION_ACCOMPTE_MINI . " € et " . $orderResteAPayer . "€", "error" => true)));
        }

        $isOk = $orderGestionPaymentManager->updateAccompte($id_order, $accompte);
        if ($isOk) {
            $orderGestionEcheancierManager = new OrderGestionEcheancierManager();
            $isOk = $orderGestionEcheancierManager->createEcheances($id_order);
        }

        if ($isOk) {
            die(Tools::jsonEncode(array("message" => "Update accompte success. Order " . $id_order . " Accompte : " . $accompte, "error" => false)));
        } else {
            die(Tools::jsonEncode(array("message" => "Update accompte error. Order " . $id_order . " Accompte : " . $accompte, "error" => false)));
        }
    }

    /**
     * Update Echeance value
     */
    public function ajaxProcessUpdateEcheance()
    {
        $id_order = (int)Tools::getValue("id_order");
        $number_echeance = $this->nombreEcheance((int)Tools::getValue("number_echeance"));

        $order = new Order((int)$id_order);
        if (null === $order) {
            die(Tools::jsonEncode(array("message" => "La commande n'existe pas", "error" => true)));
        }

        $orderGestionPaymentManager = new OrderGestionPaymentManager();
        $numberEcheanceMini = $orderGestionPaymentManager->getNumberEcheancesMini($id_order);

//        if ($number_echeance < $numberEcheanceMini) {
//            die(Tools::jsonEncode(array("message" => "Nombre d'échéances mini : " . $numberEcheanceMini, "error" => true)));
//        }

        $isOk = $orderGestionPaymentManager->updateEcheance($id_order, $number_echeance);

        if ($isOk) {
            $orderGestionEcheancierManager = new OrderGestionEcheancierManager();
            $isOk = $orderGestionEcheancierManager->createEcheances($id_order);
        }

        if ($isOk) {
            die(Tools::jsonEncode(array("message" => "Update échéance success. Order : " . $id_order . " Echéance : " . $number_echeance, "error" => false)));
        } else {
            die(Tools::jsonEncode(array("message" => "Update échéance error. Order : " . $id_order . " Echéance : " . $number_echeance, "error" => false)));
        }

    }

    public function ajaxProcessUpdateInputEcheance()
    {
        $inputValues = Tools::getValue("inputValues");
        $id_order_gestion_echeancier = (int)$inputValues['id_order_gestion_echeancier'];
        $input_value = htmlspecialchars($inputValues['input_value']);

        $messageRetour = array('message' => '', 'error' => true);
        if (!empty($id_order_gestion_echeancier)) {
            $orderEcheancier = new OrderGestionEcheancier($id_order_gestion_echeancier);

            switch ($inputValues['input_name']) {
                case "payment_date" :
                    $input_value = str_replace("/", "-", $input_value);
                    $orderEcheancier->payment_date = date("Y-m-d", strtotime($input_value));
                    $orderEcheancier->update();
                    $messageRetour['message'] = "payment_date updated " . $orderEcheancier->payment_date;
                    break;
                case "payment_method" :
                    $orderEcheancier->payment_method = $input_value;
                    $orderEcheancier->update();
                    $messageRetour['message'] = "payment_method updated " . $orderEcheancier->payment_method;
                    break;
                case "payment_transaction_id" :
                    $orderEcheancier->payment_transaction_id = (int)$input_value;
                    $orderEcheancier->update();
                    $messageRetour['message'] = "payment_transaction_id updated " . $orderEcheancier->payment_transaction_id;
                    break;
                case "payment_amount" :
                    $orderEcheancier->payment_amount = (str_replace(",", ".", $input_value)) * 100;
                    $orderEcheancier->update();
                    $messageRetour['message'] = "payment_amount updated " . $orderEcheancier->payment_amount;
                    break;
                case "gestionSubmitDelete":
                    $orderEcheancier->delete();
                    $messageRetour['message'] = "Echeance deleted " . $input_value;
                    break;
                default:
                    $messageRetour['message'] = "Il n'y a pas de valeur correspondante.";
                    $messageRetour['error'] = false;
            }
        }

        die(Tools::jsonEncode($messageRetour));
    }


    private function getResteAPayer(Order $order)
    {
        return round($order->total_paid_tax_incl - $order->total_paid_real, 2);
    }

    /**
     * Retourne le nombre de payments déjà éffectués
     *
     * @param Order $order
     * @return int
     */
    private function getPaymentsNumber(Order $order)
    {
        $payments = $order->getOrderPayments();

        return count($payments);
    }

    /**
     * Renvoie le nombre d'écheance par defaut si $numberEcheances == 0
     *
     * @param $numberEcheances
     * @return int
     */
    private function nombreEcheance($numberEcheances)
    {
        return ($numberEcheances == 0)
            ? AdminGestionPaiementsController::CDGESTION_NUMBER_ECHEANCE_DEFAULT
            : $numberEcheances;
    }

    private function getProfile()
    {
        $id_profile = $this->context->employee->id_profile;

        return Profile::getProfileAccess($id_profile, (int)Tab::getIdFromClassName('AdminGestionPaiements'));
    }
}