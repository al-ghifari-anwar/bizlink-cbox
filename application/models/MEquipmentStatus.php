<?php

class MEquipmentStatus extends CI_Model
{
    public function get()
    {
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getByName($name)
    {
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get_where('tb_equipment_status', ['name_equipment' => $name])->result_array();

        return $result;
    }

    public function getAllBatch()
    {
        $this->db->group_by('tb_equipment_status.no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }
}
