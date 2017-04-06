<?php

require_once __DIR__ . "/../../classes/models/OrderGestionEcheancier.php";

class OrderGestionEcheancierManager
{
    /**
     * @param $id_order
     * @return int
     */
    public function getNumberEcheancesAVenir($id_order)
    {
        $echeanciers = new OrderGestionEcheancier();
        $numberEcheances = $echeanciers->getNumberEcheancesAVenirByIdOrder($id_order);

        return $numberEcheances;
    }

    public function getEcheancierAVenir($id_order)
    {
        $echeancier = new OrderGestionEcheancier();
        $echeances = $echeancier->getAllEcheancesAVenirByIdOrder($id_order);

        return $echeances;
    }

    public function createEcheances($id_order)
    {
        $orderGestionPayment = new OrderGestionPayment();
        $gestionPayment = $orderGestionPayment->getOrderGestionPaymentByIdOrder($id_order);
        $order = new Order($id_order);
        $resteAPayer = $order->total_paid_tax_incl - $order->total_paid_real;
        $montantEcheances = array();

        if ($gestionPayment->accompte > 0) {

        } else {
            $montantEcheances = $this->calculMontantEcheances($resteAPayer, $gestionPayment->number_echeance, $order->total_paid_tax_incl);
        }
        return true;
    }

    private function calculMontantEcheances($resteAPayer, $number_echeance, $total_paid_tax_incl)
    {
        $echeances = array();
        $montantEcheance = $resteAPayer / $number_echeance;
        $echeanceMini = round(($total_paid_tax_incl * AdminGestionPaiementsController::CDGESTION_ACCOMPTE_POURCENTAGE_MINI),2);
        if ($montantEcheance > $echeanceMini && $montantEcheance > AdminGestionPaiementsController::CDGESTION_ACCOMPTE_MINI) {
            $echeances = array_fill(0, $number_echeance - 1, round($montantEcheance, 2));
            array_unshift($echeances, ($resteAPayer - (array_sum($echeances))));
        }
        // TODO reprendre ici
        var_dump($echeances);
        die();
        return $echeances;
    }
}