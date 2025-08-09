<?php


class MEmergency extends CI_Model
{
    public function create($emergencyData)
    {
        $query = $this->db->insert('tb_emergency', $emergencyData);

        if ($query) {
            return true;
        } else {
            return true;
        }
    }
}
