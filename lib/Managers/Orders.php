<?php

/**
 * Description of Orders
 *
 * @author gregzorb
 */
class Orders extends Crud
{

    public function createList($data = array())
    {
        if (empty($data)) return false;

        foreach ($data as $dRow) {
            $comment = (isset($dRow['comment']) ?  "'".$dRow['comment']."'," : "null,");
            $iRows[] = "( "
                .$dRow['origin_id'].", "
                .$dRow['view_id'].", "
                ."'".$dRow['status']."',"
                ."'".$dRow['origin_status']."',"
                .$comment
                ."'".$dRow['last_date']."'"
                .")";
        }

        $query = "INSERT INTO orders (origin_id, view_id, status, origin_status, comment, last_date) VALUES ".implode(",", $iRows);
        return $this->db->query($query);
    }


    public function updateStatus($data)
    {
        $query  = "UPDATE orders SET status = :status, "
            . "origin_status = :origin_status, "
            . "comment = :comment, "
            . "last_date = :last_date, "
            . "last_update_date = NOW() WHERE origin_id = :origin_id";
        return $this->db->query($query,
            array(
            "status" => $data['status'],
            "origin_status" => $data['origin_status'],
            "comment" => $data['comment'],
            "last_date" => $data['last_date'],
            "origin_id" => $data['origin_id'],
        ));
    }

    public function getAll($params = array())
    {
        $query = "select * from orders ";
        if (isset($params['filters'])) {
            $query .= "where ";

            $i = 0;
            foreach ($params['filters'] as $field => $filter){
                ++$i;
                $query .= "$field $filter ";
                if ($i != count($params['filters']) ) $query .= "and ";
            }
        }
        if (isset($params['limit'])) $query .= " limit ".$params['limit'];
        if (isset($params['offset'])) $query .= " offset ".$params['offset'];
        return $this->db->query($query);
    }

    public function countAll()
    {
        $source = $this->db->query("select count(id) as c__id from orders");
        if ( !empty($source)){
            return $source[0]['c__id'];
        }
        return 0;
    }

    public function getViewIdList()
    {
        $result = false;
        $source = $this->db->query("select origin_id, view_id from orders");
        if (!empty($source)){
            foreach ($source as $s){
                $result[$s['origin_id']] = $s['view_id'];
            }
        }
        return $result;
    }

    public function getByOriginIds($ids = array())
    {
        return $this->db->query("select * from orders where origin_id IN ( ".implode(",", $ids)."  )");
    }

    public function getListByOriginIds($ids = array())
    {
        $source  = $this->db->query("select origin_id from orders where origin_id IN ( ".implode(",", $ids)."  )");
        if ( !empty($source)){
            return array_column($source, 'origin_id');
        }
        return false;
    }

    public function getMaxId()
    {
        $source = $this->db->query("select MAX(view_id) as m__view_id from orders");
        if ( !empty($source)){
            return $source[0]['m__view_id'];
        }
        return 0;
    }

}