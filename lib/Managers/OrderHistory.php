<?php

/**
 * Description of OrderHistory
 *
 * @author gregzorb
 */
class OrderHistory extends Crud
{

    public function moveFromOrders()
    {
        $this->db->beginTransaction();
        $query  = "insert into order_history (origin_id, view_id, status, origin_status, last_date)
            select origin_id, view_id, status, origin_status, last_date from orders
            where last_date < '".date("Y-m-d 00:00:00")."'";
        $insert = $this->db->query($query);
        if (!$insert) {
            $this->db->rollBack();
            return false;
        }

        $query = "delete from orders where last_date < '".date("Y-m-d 00:00:00")."'";
        if (!$this->db->query($query)) {
            $this->db->rollBack();
            return false;
        }

        $this->db->executeTransaction();
        return true;
    }
}