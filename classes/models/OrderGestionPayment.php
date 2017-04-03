<?php

/**
 * Class OrderGestionPayment
 *
 * Module cdgestionpaiement
 * Utiliser pour la gestion des echéances dans la page commande
 */
class OrderGestionPayment extends ObjectModel
{
    /**
     * OrderGestionPayment Primary key
     */
    public $id_order_gestion_payment;

    /**
     * Order id
     */
    public $id_order;

    /**
     * Number of echeance
     */
    public $number_echeance;

    /**
     * Accompte of order
     */
    public $accompte;


    public static $definition = array(
        'table' => 'order_gestion_payment',
        'primary' => 'id_order_gestion_payment',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'number_echeance' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'accompte' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat')
        )
    );


}