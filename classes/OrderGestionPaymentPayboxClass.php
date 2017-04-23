<?php

class OrderGestionPaymentPayboxClass extends ObjectModel
{
    public $id_order_gestion_payment_paybox;

    public $id_order;

    public $id_order_gestion_echeancier;

    public $id_order_payment;

    public $transaction_id;

    public $date_of_issue;

    public $reference;

    public $amount;

    public $status;

    public $checked;

    public static $definition = array(
        'table' => 'order_gestion_payment_paybox',
        'primary' => 'id_order_gestion_payment_paybox',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_order_gestion_echeancier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_order_payment' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'transaction_id' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'date_of_issue' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => true),
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'amount' => array('type' => self::TYPE_FLOAT, 'validate' => 'isFloat'),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true),
            'checked' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        )
    );

    public static function echeanceIsChecked($id_order_gestion_echeancier)
    {
        $sql = "SELECT COUNT(*) FROM `"._DB_PREFIX_."order_gestion_payment_paybox`
                WHERE id_order_gestion_echeancier = ".(int)$id_order_gestion_echeancier;
        $req = DB::getInstance()->getValue($sql);

        return (bool)$req;
    }

}