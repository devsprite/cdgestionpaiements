<?php

class OrderGestionPaymentManager
{
    public function updateAccompte($id_order, $accompte)
    {
        $isOk = true;
        $orderGestionPaymentManager = new OrderGestionPayment();
        $orderGestionPayment = $orderGestionPaymentManager->getOrderGestionPaymentByIdOrder($id_order);

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
        $orderGestionPaymentManager = new OrderGestionPayment();
        $orderGestionPayment = $orderGestionPaymentManager->getOrderGestionPaymentByIdOrder($id_order);

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
        $orderGestionPayment = new OrderGestionPayment();
        $order = $orderGestionPayment->getOrderGestionPaymentByIdOrder((int)$id_order);

        if (null === $order) {
            return null;
        }

        return $order->accompte;
    }

    public function getNumberEcheancesTotalByOrder($id_order)
    {
        $orderGestionPayment = new OrderGestionPayment();
        $order = $orderGestionPayment->getOrderGestionPaymentByIdOrder((int)$id_order);

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

    public function getEcheancier($id_order)
    {
        $echeancierManager = new OrderGestionEcheancierManager();
        $echeancierAVenir = $echeancierManager->getEcheancierAVenir($id_order);
        $echeancier = array();
        if ($echeancierAVenir) {
            $echeancier = $this->formatEcheances($echeancierAVenir);
        }

        return $echeancier;
    }

    private function formatEcheances($echeancierAVenir)
    {
        $echeances = array();
        $echeance = array(
            'idEcheancier' => 0,
            'btnSubmitType' => '',
            'btnSubmitName' => '',
            'btnSubmitText' => '',
            'paymentDate' => '',
            'paymentMethods' => array(),
            'paymentMethod' => '',
            'paymentTransactionId' => '',
            'checked' => '',
            'paymentAmount' => '0',
            'invoices' => array(array()),
        );

        foreach ($echeancierAVenir as $echeanceAVenir) {
            $echeance['idEcheancier'] = $echeanceAVenir['id_order_gestion_echeancier'];
            $echeance['btnSubmitType'] = $this->btnSubmitType($echeanceAVenir['checked']);
            $echeance['btnSubmitName'] = $this->btnSubmitName($echeanceAVenir['checked']);
            $echeance['btnSubmitText'] = $this->btnSubmitText($echeanceAVenir['checked']);
            $echeance['paymentDate'] = $echeanceAVenir['payment_date'];
            $echeance['paymentMethods'] = AdminGestionPaiementsController::CDGESTION_PAYMENT_METHOD;
            $echeance['paymentMethod'] = $echeanceAVenir['payment_method'];
            $echeance['paymentTransactionId'] = $echeanceAVenir['payment_transaction_id'];
            $echeance['checked'] = $this->paymentIsChecked($echeanceAVenir['id_order_gestion_echeancier']);
            $echeance['paymentAmount'] = $echeanceAVenir['payment_amount'];
            $echeance['invoices'] = $this->paymentInvoices($echeanceAVenir['id_order']);

            $echeances[] = $echeance;
        }

        return $echeances;
    }

    private function btnSubmitType($checked)
    {
        return ($checked) ? 'success' : 'danger';
    }

    private function btnSubmitName($checked)
    {
        return ($checked) ? 'gestionSubmitValider' : 'gestionSubmitAjouter';
    }

    private function btnSubmitText($checked)
    {
        return ($checked) ? 'Valider' : 'Ajouter';
    }

    private function paymentIsChecked($id_order_gestion_echeancier)
    {
        return OrderGestionPaymentPaybox::echeanceIsChecked($id_order_gestion_echeancier);
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

}