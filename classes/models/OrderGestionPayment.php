<?php

/**
 * Class OrderGestionPayment
 *
 * Module cdgestionpaiement
 * Utiliser pour la gestion des echéances dans la page commande
 */
class OrderGestionPayment
{
    /**
     * OrderGestionPayment Primary key
     */
    private $id_order_gestion_payment;

    /**
     * Order id
     */
    private $id_order;

    /**
     * Number of echeance
     */
    private $number_echeance;

    /**
     * Accompte of order
     */
    private $accompte;


    /**
     * OrderGestionPayment constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        if ($id != null) {
            return $this->getOrderGestionPaymentById($id);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getIdOrderGestionPayment()
    {
        return $this->id_order_gestion_payment;
    }

    /**
     * @return int
     */
    public function getIdOrder()
    {
        return $this->id_order;
    }

    /**
     * @param int $id_order
     */
    public function setIdOrder($id_order)
    {
        $this->id_order = $id_order;
    }

    /**
     * @return int
     */
    public function getNumberEcheance()
    {
        return $this->number_echeance;
    }

    /**
     * @param int $number_echeance
     */
    public function setNumberEcheance($number_echeance)
    {
        $this->number_echeance = $number_echeance;
    }

    /**
     * @return float
     */
    public function getAccompte()
    {
        return $this->accompte;
    }

    /**
     * @param float $accompte
     */
    public function setAccompte($accompte)
    {
        $this->accompte = $accompte;
    }

    /**
     * @param int $id
     * @return OrderGestionPayment|null
     */
    public function getOrderGestionPaymentById($id)
    {
        $sql = "SELECT id_order_gestion_payment, id_order, number_echeance, accompte
                FROM `" . _DB_PREFIX_ . "order_gestion_payment` 
                WHERE `id_order_gestion_payment` = " . (int)$id . " ;";
        $obj = DB::getInstance()->getRow($sql);

        if (!$obj) {
            return null;
        }

        foreach ($obj as $key => $value) {
                $this->{$key} = $value;
        }

        return $this;
    }
}