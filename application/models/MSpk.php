<?php

class MSpk extends CI_Model
{
    public function get()
    {
        $result = $this->db->get('tb_spk')->result_array();

        return $result;
    }

    public function getToday()
    {
        $result = $this->db->get_where('tb_spk', ['date_spk' => date('Y-m-d')])->row_array();

        return $result;
    }
}
