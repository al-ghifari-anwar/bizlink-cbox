<?php

class MQontak extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_qontak', 1)->row_array();

        return $result;
    }
}
