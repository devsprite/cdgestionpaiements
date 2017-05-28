<?php

require_once __DIR__ . "/../../classes/OrderGestionPaymentPayboxClass.php";

class OrderGestionPaymentManager
{
    public function __construct()
    {
        $this->context = Context::getContext();
    }


    public function updateAccompte($id_order, $accompte)
    {
        $isOk = true;
        $orderGestionPayment = OrderGestionPayment::getOrderGestionPaymentByIdOrder($id_order);

        if (null === $orderGestionPayment->id_order) {
            $orderGestionPayment = new OrderGestionPayment();
            $orderGestionPayment->id_order = (int)$id_order;
            $orderGestionPayment->accompte = (float)$accompte;
            $isOk = $orderGestionPayment->add();
        } else {
            $orderGestionPayment->accompte = (float)$accompte;
            $isOk = $orderGestionPayment->update();
        }

        return $isOk;
    }

    public function updateEcheance($id_order, $number_echeance)
    {
        $isOk = true;
        $orderGestionPayment = OrderGestionPayment::getOrderGestionPaymentByIdOrder($id_order);

        if (null === $orderGestionPayment->id_order) {
            $orderGestionPayment = new OrderGestionPayment();
            $orderGestionPayment->id_order = (int)$id_order;
            $orderGestionPayment->number_echeance = (float)$number_echeance;
            $isOk = $orderGestionPayment->add();
        } else {
            $orderGestionPayment->number_echeance = (float)$number_echeance;
            $isOk = $orderGestionPayment->update();
        }

        return $isOk;
    }


    public function getAccompteByOrder($id_order)
    {
        $order = OrderGestionPayment::getOrderGestionPaymentByIdOrder((int)$id_order);

        if (null === $order) {
            return null;
        }

        return $order->accompte;
    }

    public function getNumberEcheancesTotalByOrder($id_order)
    {
        $order = OrderGestionPayment::getOrderGestionPaymentByIdOrder((int)$id_order);

        if (null === $order) {
            return 4;
        }

        return $order->number_echeance;
    }

    /**
     * @param $id_order
     * @return int
     */
    public function getNumberEcheancesMini($id_order)
    {
        $echeancesPayed = $this->getNumberEcheancesPayed($id_order);
        $order = new Order($id_order);
        $needToPaid = $order->total_paid_tax_incl - $order->total_paid_real;

        if ($needToPaid <= 0) {
            return $echeancesPayed;
        }

        return $echeancesPayed;
    }

    /**
     * @param $id_order
     * @return int
     */
    public function getNumberEcheancesPayed($id_order)
    {
        $order = new Order($id_order);
        $payments = $order->getOrderPayments();

        return count($payments);
    }

    /**
     * @param $id_order
     * @return int
     */
    public function getNumberEcheancesMax($id_order)
    {
        $echeancesMini = $this->getNumberEcheancesMini($id_order);
        $order = new Order($id_order);
        $needToPaid = $order->total_paid_tax_incl - $order->total_paid_real;

        if ($needToPaid <= 0) {
            return $echeancesMini;
        }

        return $echeancesMini + 10;
    }

    public function getEcheancier($order)
    {
        $echeancierManager = new OrderGestionEcheancierManager();
        $echeancierAVenir = $echeancierManager->getEcheancierAVenir($order['id_order']);
        $echeancier = array();
        if ($echeancierAVenir) {
            $echeancier = $this->formatEcheances($echeancierAVenir, $order);
        }

        return $echeancier;
    }

    private function formatEcheances($echeancierAVenir, $order)
    {
        $echeances = array();
        $echeance = array(
            'idEcheancier' => 0,
            'btnSubmitClass' => '',
            'btnSubmitName' => '',
            'btnSubmitText' => '',
            'paymentDate' => '',
            'paymentMethods' => array(),
            'paymentMethod' => '',
            'paymentTransactionId' => '',
            'checked' => '',
            'paymentAmount' => '0',
            'invoices' => array(array()),
            'paiementPaybox' => array(),
            'disabled' => 'disabled',
            'delete' => 'false',
            'valider' => 'false'
        );
        foreach ($echeancierAVenir as $echeanceAVenir) {
            $echeance['idEcheancier'] = $echeanceAVenir['id_order_gestion_echeancier'];
            $echeance['paymentDate'] = $echeanceAVenir['payment_date'];
            $echeance['paiementPaybox'] = $this->getPaymentPaybox($echeance);
            $echeance['paymentMethods'] = AdminGestionPaiementsController::CDGESTION_PAYMENT_METHOD;
            $echeance['paymentMethod'] = $echeanceAVenir['payment_method'];
            $echeance['paymentTransactionId'] = $echeance['paiementPaybox']['transaction_id'];
            $echeance['checked'] = $this->paymentIsChecked($echeanceAVenir['id_order_gestion_echeancier']);
            $echeance['paymentAmount'] = number_format(($echeanceAVenir['payment_amount'] / 100), 2);
            $echeance['invoices'] = $this->paymentInvoices($echeanceAVenir['id_order']);
            $echeance['disabled'] = $this->disabled($order);
            $echeance['delete'] = $this->delete($order);
            $echeance['valider'] = $this->valider($order, $echeanceAVenir);

            $echeanceTmp = new OrderGestionEcheancier($echeance['idEcheancier']);
            $echeanceTmp->payment_transaction_id = $echeance['paymentTransactionId'];
            $echeanceTmp->save();

            $echeances[] = $echeance;
        }


        return $echeances;
    }

    private function paymentIsChecked($id_order_gestion_echeancier)
    {
        return OrderGestionPaymentPayboxClass::echeanceIsChecked($id_order_gestion_echeancier);
    }

    private function paymentInvoices($id_order)
    {
        $invoice = array(
            'invoiceNumber' => 0,
            'invoiceFormated' => ''
        );
        $order = new Order($id_order);
        if ($order->hasInvoice()) {
            $invoiceNumber = $order->invoice_number;
            $invoice['invoiceNumber'] = $invoiceNumber;
            $invoice['invoiceFormated'] = '#FA' . str_pad($invoiceNumber, 6, '0', STR_PAD_LEFT);
        }

        return $invoice;
    }

    private function getPaymentPaybox($echeance)
    {
        $payment = array();
        $id_order = OrderGestionEcheancier::getIdOrderByIdEcheancier($echeance['idEcheancier']);
        $paymentPaybox = OrderGestionPaymentPayboxClass::getEcheance($id_order, $echeance['paymentDate']);
        if ($paymentPaybox) {
            $payment['id_order_gestion_payment_paybox'] = $paymentPaybox['id_order_gestion_payment_paybox'];
            $payment['date_of_issue'] = $paymentPaybox['date_of_issue'];
            $payment['amount'] = $paymentPaybox['amount'] / 100;
            $payment['checked'] = $paymentPaybox['checked'];
            $payment['transaction_id'] = $paymentPaybox['transaction_id'];
        }

        return $payment;
    }

    private function disabled($order)
    {
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if (($order['profil']['edit'] == 0 || ($order['profil']['edit'] == 0 && $valid == 1)) && $order['profil']['id_profile'] != 1) {
            return 'disabled';
        }

        return '';
    }

    private function delete($order)
    {
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if ((($order['profil']['delete'] == 0 || ($order['profil']['delete'] == 1 && $valid == 0)) && $order['profil']['id_profile'] != 1)) {
            return false;
        }
        return true;
    }

    private function valider($order, $echeance)
    {
        $return = false;
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if ((($order['profil']['add'] == 0 || ($order['profil']['add'] == 1 && $valid == 0)) && $order['profil']['id_profile'] != 1)) {
            $return = false;
        } else {
//            $echeancePaybox = OrderGestionPaymentPayboxClass::getEcheance($order['id_order'], $echeance['payment_date']);
//            if($echeancePaybox !== false) {
//                if($echeancePaybox['amount'] === $echeance['payment_amount']) {
                    $return = true;
//                }
//            }
        }

        return $return;
    }

}