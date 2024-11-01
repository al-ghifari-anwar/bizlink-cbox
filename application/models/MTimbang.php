<?php

class MTimbang extends CI_Model
{
    public function getByBatch($no_batch)
    {
        $result = $this->db->get_where('tb_timbang', ['no_batch' => $no_batch])->result_array();

        return $result;
    }
}
