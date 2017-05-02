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

    const CDGESTION_DAYS_BETWEEN_ECHEANCE = 2;

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

    public static function getEcheance($id_order, $paymentDate)
    {
        $sql = "SELECT * 
                FROM `"._DB_PREFIX_."order_gestion_payment_paybox` 
                WHERE id_order = " . $id_order . "
                AND status = 'Télécollecté' ";

        $payments = DB::getInstance()->executeS($sql);

        if (count($payments) > 0) {
            foreach ($payments as $payment) {
                if (self::isExistAnEcheance($paymentDate, $payment['date_of_issue'])) {
                    return $payment;
                }
            }
        }

        return false;
    }

    /**
     * Si une écheance existe dans le creneau de jours définit, return true
     * @param $payment Payment Paybox
     * @param $echeance Echeances de la commande
     * @return bool
     */
    public static function isExistAnEcheance($payment, $echeance)
    {
        $datePaymentPaybox = new DateTime($payment);
        $dateEcheanceOrder = new DateTime($echeance);
        $dayBetweenEcheance = $datePaymentPaybox->diff($dateEcheanceOrder);

        if ($dayBetweenEcheance->days < self::CDGESTION_DAYS_BETWEEN_ECHEANCE) {
            return true;
        }

        return false;
    }

}