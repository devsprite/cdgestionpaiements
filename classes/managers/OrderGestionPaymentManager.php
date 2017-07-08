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
            $orderGestionPayment->accompte = $accompte;
            $isOk = $orderGestionPayment->add();
        } else {
            $orderGestionPayment->accompte = $accompte;
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
            $orderGestionPayment->number_echeance = (int)$number_echeance;
            $isOk = $orderGestionPayment->add();
        } else {
            $orderGestionPayment->number_echeance = (int)$number_echeance;
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
        // Changer ici pour le nombre d'echeance du select si prmiere fois
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
        // Changer ici pour le nombre d'echeances du select
        $nbr = 10;
        $id_profil = $this->context->employee->id_profile;

        if($id_profil == 4) {
            $nbr = 4;
        }

        return $echeancesMini + $nbr;
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
            'valider' => 'false',
            'accomptePayed' => false
        );

        foreach ($echeancierAVenir as $key => $echeanceAVenir) {
            $echeance['idEcheancier'] = $echeanceAVenir['id_order_gestion_echeancier'];
            $echeance['paymentDate'] = $echeanceAVenir['payment_date'];
            $echeance['paiementPaybox'] = $this->getPaymentPaybox($echeance);
            $echeance['paymentMethods'] = AdminGestionPaiementsController::CDGESTION_PAYMENT_METHOD;
            $echeance['paymentMethod'] = $echeanceAVenir['payment_method'];
            $echeance['paymentTransactionId'] = $echeance['paiementPaybox']['transaction_id'];
            $echeance['paymentAmount'] = number_format($echeanceAVenir['payment_amount'], 2);
            $echeance['checked'] = $this->paymentIsChecked($echeanceAVenir['id_order_gestion_echeancier']);
            $echeance['accomptePayed'] = ($key == 0 && $echeance['checked'] == false) ? true : false;
            $echeance['invoices'] = $this->paymentInvoices($echeanceAVenir['id_order']);
            $echeance['payed'] = ($echeanceAVenir['payed'] == 1) ? "payed" : "";
            $echeance['disabled'] = $this->disabled($order, $echeanceAVenir);
            $echeance['delete'] = $this->delete($order, $echeanceAVenir);
            $echeance['valider'] = $this->valider($order, $echeanceAVenir, $echeance);


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
            $payment['amount'] = $paymentPaybox['amount'];
            $payment['checked'] = $paymentPaybox['checked'];
            $payment['transaction_id'] = $paymentPaybox['transaction_id'];
        }

        return $payment;
    }

    private function disabled($order, $echeance)
    {
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if (($order['profil']['edit'] == 0 ||
            ($order['profil']['edit'] == 1 && $valid == 1)) ||
            $echeance['payed'] == 1
        ) {
            return 'disabled';
        }

        return '';
    }

    private function delete($order, $echeance)
    {
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if ((($order['profil']['delete'] == 0 ||
            ($order['profil']['delete'] == 1 && $valid == 1)) && $order['profil']['id_profile'] != 1 ||
            $echeance['payed'] == 1)
        ) {
            return false;
        }
        return true;
    }

    private function valider($order, $echeanceAvenir, $echeance)
    {
        $return = false;
        $order_ = new Order($order['id_order']);
        $valid = $order_->valid;
        if ((($order['profil']['add'] == 0 ||
            ($order['profil']['add'] == 1 && $valid == 0)) && $order['profil']['id_profile'] != 1)||
            $echeanceAvenir['payed'] == 1) {
            $return = false;
        }else{
            $echeancePaybox = OrderGestionPaymentPayboxClass::getEcheance($order['id_order'], $echeanceAvenir['payment_date']);
            if($echeancePaybox !== false) {
                if($echeancePaybox['amount'] === $echeanceAvenir['payment_amount']) {
                    $return = true;
                }
            }
            if($echeance['accomptePayed'] == true) {
                $return = true;
            }
        }
        if($order['profil']['id_profile'] == 1 && $echeanceAvenir['payed'] == 0) {
            $return = true;
        }

        return $return;
    }

    public function updateEcheances(OrderGestionPayment $orderGestion)
    {
        $this->updateEcheance($orderGestion->id_order, $orderGestion->number_echeance - 1);
        $echeances = $this::getNumberEcheancesTotalByOrder($orderGestion->id_order);
        if ($echeances > 0) {
            $accompte = $orderGestion->accompte;
            $order = new Order($orderGestion->id_order);
            $resteAPayer = $order->total_paid_tax_incl - $order->total_paid_real;
            if ($accompte > 0) {
                // calcule echances avec accompte
                $echeancierManager = new OrderGestionEcheancierManager();
                $echeancierAVenir = $echeancierManager->getEcheancierAVenir($orderGestion->id_order);
                $nbrEcheance = count($echeancierAVenir);
                $nbrEcheance--;
                $resteAPayer = $resteAPayer - $accompte;
                $montant = $this->calculMontantEcheances($resteAPayer, $nbrEcheance);
                foreach ($echeancierAVenir as $key => $echeancier) {
                    if ($accompte !== false) {
                        $this->updateEcheancier($accompte, $echeancier['id_order_gestion_echeancier']);
                        $accompte = false;
                    } else {
                        $this->updateEcheancier($montant[$key-1], $echeancier['id_order_gestion_echeancier']);
                    }
                }
            } else {
                $echeancierManager = new OrderGestionEcheancierManager();
                $echeancierAVenir = $echeancierManager->getEcheancierAVenir($orderGestion->id_order);
                $nbrEcheance = count($echeancierAVenir);
                $montant = $this->calculMontantEcheances($resteAPayer, $nbrEcheance);
                foreach ($echeancierAVenir as $key => $echeancier) {
                    $this->updateEcheancier($montant[$key], $echeancier['id_order_gestion_echeancier']);
                }
            }
        }
    }

    private function calculMontantEcheances($resteAPayer, $number_echeance)
    {
        $echeances = array();
        $montantEcheance = $resteAPayer / $number_echeance;
        $echeances = array_fill(0, $number_echeance - 1, round($montantEcheance, 2));
        array_unshift($echeances, ($resteAPayer - (array_sum($echeances))));

        return $echeances;
    }

    public function updateEcheancier($montant, $id_echeancier) {
    $sql = "UPDATE `"._DB_PREFIX_."order_gestion_echeancier` 
                            SET payment_amount = ".$montant."
                            WHERE 
                            id_order_gestion_echeancier =".$id_echeancier;
    $req = DB::getInstance()->execute($sql);
    }

    public function deleteEcheancier($id_order)
    {
        $orderGestion = $this->getEcheancierByIdOrder($id_order);
        $this->deleteEcheances($orderGestion->id_order_gestion_payment);
        $orderGestion->delete();
    }

    private function getEcheancierByIdOrder($id_order)
    {
        $sql = "SELECT id_order_gestion_payment FROM `"._DB_PREFIX_."order_gestion_payment` WHERE id_order = " . (int)$id_order;
        $id = DB::getInstance()->getValue($sql);
        return new OrderGestionPayment($id);
    }

    private function deleteEcheances($id_order_gestion_payment)
    {
        $sql = "DELETE FROM `"._DB_PREFIX_."order_gestion_echeancier` WHERE id_order_gestion_payment = " . (int)$id_order_gestion_payment;
        $req = DB::getInstance()->execute($sql);
    }

}