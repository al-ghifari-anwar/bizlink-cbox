<?php

class MFormula extends CI_Model
{
    public $id_product;
    public $target_formula;
    public $fine_formula;
    public $coarse_formula;
    public $kode_material;
    public $name_material;
    public $time_target;
    public $urutan_formula;
    public $updated_at;

    public function get()
    {
        $result = $this->db->get('tb_formula')->result_array();

        return $result;
    }

    public function getById($id)
    {
        $result = $this->db->get_where('tb_formula', ['id_formula' => $id])->row_array();

        return $result;
    }

    public function getJmlByProductId($id_product)
    {
        $result = $this->db->get_where('tb_formula', ['id_product' => $id_product])->num_rows();

        return $result;
    }

    public function getByProductId($id_product)
    {
        $result = $this->db->get_where('tb_formula', ['id_product' => $id_product])->result_array();

        return $result;
    }

    public function getByProductIdAndMaterial($id_product, $kode_material)
    {
        $result = $this->db->get_where('tb_formula', ['id_product' => $id_product, 'kode_material' => $kode_material])->row_array();

        return $result;
    }

    public function create()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->id_product = $post['id_product'];
        $this->target_formula = $post['target_formula'];
        $this->fine_formula = $post['fine_formula'];
        $this->coarse_formula = isset($post['coarse_formula']) ? $post['coarse_formula'] : 0;
        $this->kode_material = $post['kode_material'];
        $this->name_material = $post['name_material'];
        $this->time_target = $post['time_target'];
        $this->urutan_formula = isset($post['urutan_formula']) ? $post['urutan_formula'] : 0;
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->insert('tb_formula', $this);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id)
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->id_product = $post['id_product'];
        $this->target_formula = $post['target_formula'];
        $this->fine_formula = $post['fine_formula'];
        $this->coarse_formula = isset($post['coarse_formula']) ? $post['coarse_formula'] : 0;
        $this->kode_material = $post['kode_material'];
        $this->name_material = $post['name_material'];
        $this->time_target = $post['time_target'];
        $this->urutan_formula = isset($post['urutan_formula']) ? $post['urutan_formula'] : 0;
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->update('tb_formula', $this, ['id_formula' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id)
    {
        $query = $this->db->delete('tb_formula', ['id_formula' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
