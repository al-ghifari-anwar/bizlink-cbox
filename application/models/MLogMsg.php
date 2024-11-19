<?php

class MLogMsg extends CI_Model
{
    public function get()
    {
        $this->db->order_by('date_msg', 'DESC');
        $result = $this->db->get('tb_log_msg');

        return $result;
    }

    public function createFromArray($arrayLogMsg)
    {
        $query = $this->db->insert('tb_log_msg', $arrayLogMsg);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
