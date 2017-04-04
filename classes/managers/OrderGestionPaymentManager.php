<?php

class OrderGestionPaymentManager
{
    public function updateAccompte($id_order, $accompte) {
        $isOk = true;
        $orderGestionPaymentManager = new OrderGestionPayment();
        $orderGestionPayment = $orderGestionPaymentManager->getOrderGestionPaymentByIdOrder($id_order);

        if (null === $orderGestionPayment) {
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

        if (null === $orderGestionPayment) {
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
            return null;
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




}