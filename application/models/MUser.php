<?php

class MUser extends CI_Model
{
    public $name_user;
    public $phone_user;
    public $is_active;

    public function get()
    {
        $result = $this->db->get('tb_user')->result_array();

        return $result;
    }

    public function getById($id)
    {
        $result = $this->db->get_where('tb_user', ['id_user' => $id])->row_array();

        return $result;
    }

    public function create()
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->name_user = $post['name_user'];
        $this->phone_user = $post['phone_user'];
        $this->is_active = $post['is_active'];

        $query = $this->db->insert('tb_user', $this);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function update($id)
    {
        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $this->name_user = $post['name_user'];
        $this->phone_user = $post['phone_user'];
        $this->is_active = $post['is_active'];

        $query = $this->db->update('tb_user', $this, ['id_user' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }

    public function destroy($id)
    {
        $query = $this->db->delete('tb_user', ['id_user' => $id]);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
