<?php

class MProduct extends CI_Model
{
    public $kode_product;
    public $name_product;
    public $updated_at;

    public function get()
    {
        $result = $this->db->get('tb_product')->result_array();

        return $result;
    }

    public function getById($id)
    {
        $result = $this->db->get('tb_product', ['id_product' => $id])->row_array();

        return $result;
    }

    public function create()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->kode_product = $post['kode_product'];
        $this->name_product = $post['name_product'];
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->insert('tb_product', $this);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id)
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->kode_product = $post['kode_product'];
        $this->name_product = $post['name_product'];
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->update('tb_product', $this, ['id_producr' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id)
    {
        $query = $this->db->delete('tb_product', ['id_producr' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
