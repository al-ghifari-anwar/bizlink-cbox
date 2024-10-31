<?php

class MEquipmentStatus extends CI_Model
{
    public function get()
    {
        $this->db->order_by('tb_equipment.created_at', 'DESC');
        $result = $this->db->get('tb_equipment')->result_array();

        return $result;
    }

    public function getByName($name)
    {
        $this->db->order_by('tb_equipment.created_at', 'DESC');
        $result = $this->db->get_where('tb_equipment', ['name_equipment' => $name])->result_array();

        return $result;
    }
}
