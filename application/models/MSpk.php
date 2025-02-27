<?php

class MSpk extends CI_Model
{
    public $id_product;
    public $jml_batch;
    public $ordering_spk;
    public $date_spk;
    public $desc_spk;
    public $updated_at;

    public function get()
    {
        $this->db->order_by('created_at', 'DESC');
        $result = $this->db->get('tb_spk')->result_array();

        return $result;
    }

    public function getById($id_spk)
    {
        $result = $this->db->get_where('tb_spk', ['id_spk' => $id_spk])->row_array();

        return $result;
    }

    public function getToday()
    {
        $result = $this->db->get_where('tb_spk', ['date_spk' => date('Y-m-d')])->row_array();

        return $result;
    }

    public function getByDate($date_spk)
    {
        $this->db->order_by('created_at', 'DESC');
        $result = $this->db->get_where('tb_spk', ['date_spk' => date('Y-m-d', strtotime($date_spk))])->result_array();

        return $result;
    }

    public function getPeriod($period)
    {
        if ($period == 'past') {
            $this->db->where('date_spk <', date("Y-m-d"));
        } else if ($period == 'upcoming') {
            $this->db->where('date_spk >', date("Y-m-d"));
        } else if ($period == 'now') {
            $this->db->where('date_spk', date("Y-m-d"));
        }
        $this->db->order_by('created_at', 'DESC');
        $result = $this->db->get('tb_spk')->result_array();

        return $result;
    }

    public function create()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->id_product = $post['id_product'];
        $this->jml_batch = $post['jml_batch'];
        $this->ordering_spk = $post['ordering_spk'];
        $this->date_spk = $post['date_spk'];
        $this->desc_spk = $post['desc_spk'];
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->insert('tb_spk', $this);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id_spk)
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->id_product = $post['id_product'];
        $this->jml_batch = $post['jml_batch'];
        $this->ordering_spk = $post['ordering_spk'];
        $this->date_spk = $post['date_spk'];
        $this->desc_spk = $post['desc_spk'];
        $this->updated_at = date("Y-m-d H:i:s");

        $query = $this->db->update('tb_spk', $this, ['id_spk' => $id_spk]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id_spk)
    {
        $query = $this->db->delete('tb_spk', ['id_spk' => $id_spk]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
