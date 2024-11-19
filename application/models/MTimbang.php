<?php

class MTimbang extends CI_Model
{
    public function getJmlMatByKodeProductAndBatch($kode_product, $no_batch)
    {
        $this->db->group_by('tb_timbang.kode_bahan');
        $result = $this->db->get_where('tb_timbang', ['kode_product' => $kode_product, 'no_batch' => $no_batch])->num_rows();

        return $result;
    }

    public function getByKodeProductAndBatch($kode_product, $no_batch)
    {
        $result = $this->db->get_where('tb_timbang', ['kode_product' => $kode_product, 'no_batch' => $no_batch])->result_array();

        return $result;
    }

    public function getPrdByBatch($no_batch)
    {
        $this->db->select('MAX(kode_product) AS kode_product');
        $this->db->group_by('tb_timbang.kode_product');
        $result = $this->db->get_where('tb_timbang', ['no_batch' => $no_batch])->row_array();

        return $result;
    }

    public function getByBatch($no_batch)
    {
        $result = $this->db->get_where('tb_timbang', ['no_batch' => $no_batch])->result_array();

        return $result;
    }
}
