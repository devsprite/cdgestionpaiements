<?php

class OrderGestionPaymentPaybox
{
    private $id_order_gestion_payment_paybox;

    private $id_order;

    private $id_order_gestion_echeancier;

    private $id_order_payment;

    private $transaction_id;

    private $date_of_issue;

    private $reference;

    private $amount;

    private $status;

    private $checked;

    /**
     * @return int
     */
    public function getIdOrderGestionPaymentPaybox()
    {
        return $this->id_order_gestion_payment_paybox;
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
    public function getIdOrderGestionEcheancier()
    {
        return $this->id_order_gestion_echeancier;
    }

    /**
     * @param int $id_order_gestion_echeancier
     */
    public function setIdOrderGestionEcheancier($id_order_gestion_echeancier)
    {
        $this->id_order_gestion_echeancier = $id_order_gestion_echeancier;
    }

    /**
     * @return int
     */
    public function getIdOrderPayment()
    {
        return $this->id_order_payment;
    }

    /**
     * @param int $id_order_payment
     */
    public function setIdOrderPayment($id_order_payment)
    {
        $this->id_order_payment = $id_order_payment;
    }

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @param int $transaction_id
     */
    public function setTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
    }

    /**
     * @return DateTime
     */
    public function getDateOfIssue()
    {
        return $this->date_of_issue;
    }

    /**
     * @param DateTime $date_of_issue
     */
    public function setDateOfIssue($date_of_issue)
    {
        $this->date_of_issue = $date_of_issue;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * @param boolean $checked
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;
    }

    /**
     * @param int $id
     * @return OrderGestionPaymentPaybox|null
     */
    public function getOrderGestionPaymentPayboxById($id)
    {
        $sql = "SELECT id_order_gestion_payment_paybox, id_order, id_order_gestion_echeancier, id_order_payment, transaction_id, date_of_issue, reference, amount, status, checked
                FROM `" . _DB_PREFIX_ . "order_gestion_payment_paybox`
                WHERE id_order_gestion_payment_paybox = " . (int)$id;
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