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
}