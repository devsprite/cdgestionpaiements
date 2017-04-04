<?php

class OrderGestionEcheancier extends ObjectModel
{
    public $id_order_gestion_echeancier;

    public $id_order_gestion_payment;

    public $payment_date;

    public $payment_method;

    public $payment_transaction_id;

    public $payment_amount;

    public $payed;

    public $id_employee;

    public static $definition = array(
        'table' => 'order_gestion_echeancier',
        'primary' => 'id_order_gestion_echeancier',
        'fields' => array(
            'id_order_gestion_payment' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'payment_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'payment_method' => array('type' => self::TYPE_STRING, 'validate' => 'isString'),
            'payment_transaction_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'payment_amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true),
            'payed' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'id_employee' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
        )
    );

    public function getNumberEcheancesAVenirByIdOrder($id_order)
    {
        $sql = "SELECT COUNT(id_order_gestion_echeancier) as total
                FROM `" . _DB_PREFIX_ . "order_gestion_payment` as ogp
                LEFT JOIN `" . _DB_PREFIX_ . "order_gestion_echeancier` as oge
                ON ogp.id_order_gestion_payment = oge.id_order_gestion_payment
                WHERE oge.payed = 0";

        $req = Db::getInstance()->getValue($sql);

        return $req;
    }
}