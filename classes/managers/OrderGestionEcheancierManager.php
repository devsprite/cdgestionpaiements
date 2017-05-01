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
        $echeances = OrderGestionEcheancier::getAllEcheancesAVenirByIdOrder($id_order);

        return $echeances;
    }

    public function createEcheances($id_order)
    {
        $gestionPayment = OrderGestionPayment::getOrderGestionPaymentByIdOrder($id_order);
        $order = new Order($id_order);
        $payments = $order->getOrderPayments();
        $paymentsNumber = count($payments);
        $resteAPayer = $order->total_paid_tax_incl - $order->total_paid_real;
        $montantEcheances = array();

        if ($gestionPayment->accompte > 0 && $paymentsNumber == 0) {
            $montantEcheances = $this->calculMontantEcheancesWithAccompte($gestionPayment, $resteAPayer, $order->total_paid_tax_incl);
        } else {
            $montantEcheances = $this->calculMontantEcheances($resteAPayer, $gestionPayment->number_echeance, $order->total_paid_tax_incl);
        }

        $this->deleteEcheances($gestionPayment);
        $this->insertNewEcheances($gestionPayment, $montantEcheances);

        return true; // Todo personnaliser le message de retour
    }

    private function calculMontantEcheances($resteAPayer, $number_echeance, $total_paid_tax_incl)
    {
        $echeances = array();
        $montantEcheance = $resteAPayer / $number_echeance;

        $echeanceMini = round(($total_paid_tax_incl * AdminGestionPaiementsController::CDGESTION_ACCOMPTE_POURCENTAGE_MINI), 2);
        if ($montantEcheance > $echeanceMini && $montantEcheance > AdminGestionPaiementsController::CDGESTION_ACCOMPTE_MINI) {
            $echeances = array_fill(0, $number_echeance - 1, round($montantEcheance, 2));
            array_unshift($echeances, ($resteAPayer - (array_sum($echeances))));
        } else {
            return die(Tools::jsonEncode(array('message' => 'Le montant d\'une échéance avec ces informations est trop faible : ' . round($montantEcheance, 2) . ' €', 'error' => true)));
        }

        return $echeances;
    }

    private function calculMontantEcheancesWithAccompte(OrderGestionPayment $gestionPayment, $resteAPayer, $total_paid_tax_incl)
    {
        $resteAPayerMoinsAccompte = $resteAPayer - $gestionPayment->accompte;
        $echeances = $this->calculMontantEcheances($resteAPayerMoinsAccompte, $gestionPayment->number_echeance, $total_paid_tax_incl);
        array_unshift($echeances, (float)$gestionPayment->accompte);

        return $echeances;
    }

    private function deleteEcheances(OrderGestionPayment $gestionPayment)
    {
        $sql = "DELETE FROM `" . _DB_PREFIX_ . "order_gestion_echeancier`
                WHERE id_order_gestion_payment = " . $gestionPayment->id_order_gestion_payment . "
                AND payed = 0";
        $req = DB::getInstance()->execute($sql);

        return $req;
    }

    private function insertNewEcheances(OrderGestionPayment $gestionPayment, $montantEcheances)
    {
        $dateEcheance = 0;
        foreach ($montantEcheances as $montantEcheance) {
            $echeance = new OrderGestionEcheancier();
            $echeance->id_order_gestion_payment = $gestionPayment->id_order_gestion_payment;
            $echeance->payment_date = date('Y-m-d', strtotime("+".$dateEcheance." month"));
            $echeance->payment_amount = $montantEcheance;
            $echeance->add();
            $dateEcheance++;
        }
    }
}