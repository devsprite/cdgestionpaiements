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
require_once __DIR__ . "/../../classes/models/OrderGestionEcheancier.php";
require_once __DIR__ . "/../../classes/models/OrderGestionPaymentPaybox.php";

class AdminGestionPaiementsController extends ModuleAdminController
{
    private $orderInformations = array();

    public function ajaxProcessGetOrderInformations()
    {
        $this->orderInformations['id_order'] = (int)Tools::getValue("id_order");
        $this->getOrderInformations();

        die(Tools::jsonEncode($this->orderInformations));
    }

    private function getOrderInformations()
    {
        $order = new Order($this->orderInformations['id_order']);

        $this->orderInformations['id_customer'] = (int)$order->id_customer;
        $this->orderInformations['total_paid'] = round($order->total_paid, 2);
        $this->orderInformations['orders_total_paid_tax_incl'] = round($order->total_paid_tax_incl, 2);
        $this->orderInformations['order_reste_a_payer'] = $this->getResteAPayer($order);
        $this->orderInformations['paymentsNumber'] = $this->getPaymentsNumber($order);
        $this->orderInformations['numberEcheance'] = "Faire fonction";
        $this->orderInformations['accompte'] = "Faire fonction";
    }

    public function ajaxProcessSetAccompte(){
        $accompte = (float)Tools::getValue("accompte");
        $id_order = (int)Tools::getValue("id_order");

        if (empty($id_order)) {
            die(Tools::jsonEncode(array("Error" => "id_order is empty")));
        }

        if ($accompte < 0) {
            die(Tools::jsonEncode(array("Error" => "Accompte must be positive")));
        }

        // Todo mettre à jour ou créer l'order
        die(Tools::jsonEncode(array("Success" => "Order ".$id_order." Accompte Updated : " . $accompte)));


    }

    private function getResteAPayer(Order $order)
    {
        return round($order->total_paid_tax_incl - $order->total_paid, 2);
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


}