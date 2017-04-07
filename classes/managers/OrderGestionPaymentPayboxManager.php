<?php

class OrderGestionPaymentPayboxManager
{
    public static function isOrderPayboxExistByTransactionId($transactionId)
    {
        $sql = "SELECT id_order_gestion_payment_paybox
                FROM `" . _DB_PREFIX_ . "order_gestion_payment_paybox`
                WHERE transaction_id = " . (int)$transactionId;
        $req = Db::getInstance()->getValue($sql);

        return $req;
    }
}
