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

    public function getNumberEcheanceByOrder($id_order)
    {
        $orderGestionPayment = new OrderGestionPayment();
        $order = $orderGestionPayment->getOrderGestionPaymentByIdOrder((int)$id_order);

        if (null === $order) {
            return null;
        }

        return $order->number_echeance;
    }


}