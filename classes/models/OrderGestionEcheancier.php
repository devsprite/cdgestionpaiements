<?php

class OrderGestionEcheancier
{
    private $id_order_gestion_echeancier;

    private $id_order_gestion_payment;

    private $payment_date;

    private $payment_method;

    private $payment_transaction_id;

    private $payment_amount;

    private $payed;

    private $id_employee;

    public function __construct($id = null)
    {
        if ($id) {
            return $this->getOrderGestionEcheancierById($id);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getIdOrderGestionEcheancier()
    {
        return $this->id_order_gestion_echeancier;
    }

    /**
     * @return int
     */
    public function getIdOrderGestionPayment()
    {
        return $this->id_order_gestion_payment;
    }

    /**
     * @param int $id_order_gestion_payment
     */
    public function setIdOrderGestionPayment($id_order_gestion_payment)
    {
        $this->id_order_gestion_payment = $id_order_gestion_payment;
    }

    /**
     * @return DateTime
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * @param DateTime $payment_date
     */
    public function setPaymentDate($payment_date)
    {
        $this->payment_date = $payment_date;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->payment_method;
    }

    /**
     * @param string $payment_method
     */
    public function setPaymentMethod($payment_method)
    {
        $this->payment_method = $payment_method;
    }

    /**
     * @return int
     */
    public function getPaymentTransactionId()
    {
        return $this->payment_transaction_id;
    }

    /**
     * @param int $payment_transaction_id
     */
    public function setPaymentTransactionId($payment_transaction_id)
    {
        $this->payment_transaction_id = $payment_transaction_id;
    }

    /**
     * @return float
     */
    public function getPaymentAmount()
    {
        return $this->payment_amount;
    }

    /**
     * @param float $payment_amount
     */
    public function setPaymentAmount($payment_amount)
    {
        $this->payment_amount = (float)$payment_amount;
    }

    /**
     * @return boolean
     */
    public function getPayed()
    {
        return $this->payed;
    }

    /**
     * @param boolean $payed
     */
    public function setPayed($payed)
    {
        $this->payed = $payed;
    }

    /**
     * @return int
     */
    public function getIdEmployee()
    {
        return $this->id_employee;
    }

    /**
     * @param int $id_employee
     */
    public function setIdEmployee($id_employee)
    {
        $this->id_employee = $id_employee;
    }

    /**
     * @param int $id
     * @return OrderGestionEcheancier|null
     */
    public function getOrderGestionEcheancierById($id)
    {
        $sql = "SELECT id_order_gestion_echeancier, id_order_gestion_payment, payment_date, payment_method, payment_transaction_id, payment_amount, payed, id_employee
                FROM `" . _DB_PREFIX_ . "order_gestion_echeancier`
                WHERE id_order_gestion_echeancier = " . (int)$id;
        $obj = Db::getInstance()->getRow($sql);

        if (!$obj) {
            return null;
        }

        foreach ($obj as $key => $value) {
            $this->{$key} = $value;
        }

        return $this;
    }


}