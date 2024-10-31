<?php

class MAccurate extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_accurate', 1)->row_array();

        return $result;
    }
}
