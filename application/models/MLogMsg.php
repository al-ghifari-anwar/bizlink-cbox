<?php

class MLogMsg extends CI_Model
{
    public function get()
    {
        $this->db->order_by('date_msg', 'DESC');
        $result = $this->db->get('tb_log_msg')->result_array();

        return $result;
    }

    public function getInsertedLog($to_name, $to_number, $type_msg, $message)
    {
        // $this->db->order_by('date_msg', 'DESC');
        $result = $this->db->get_where('tb_log_msg', ['to_name' => $to_name, 'to_number' => $to_number, 'type_msg' => $type_msg, 'message' => $message])->row_array();

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
