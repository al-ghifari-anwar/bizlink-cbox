<?php

class MEquipmentStatus extends CI_Model
{
    public function get()
    {
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getExisting($no_batch, $status_equipment, $name_equipment)
    {
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch, 'status_equipment' => $status_equipment, 'name_equipment' => $name_equipment])->row_array();

        return $result;
    }

    public function getById($id_equipment_status)
    {
        $result = $this->db->get_where('tb_equipment_status', ['id_equipment_status' => $id_equipment_status])->row_array();

        return $result;
    }

    public function getMixerOn($id_spk)
    {
        $this->db->group_by('tb_equipment_status.no_batch');
        $result = $this->db->get_where('tb_equipment_status', ['status_equipment' => 'ON', 'name_equipment' => 'MIXER', 'id_spk' => $id_spk])->result_array();

        return $result;
    }

    public function getMixerOnForBatchingNumber($id_product, $date)
    {
        $this->db->join('tb_spk', 'tb_spk.id_spk = tb_equipment_status.id_spk');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        // $this->db->group_by('tb_equipment_status.no_batch');
        $result = $this->db->get_where('tb_equipment_status', ['status_equipment' => 'ON', 'name_equipment' => 'MIXING TIME', 'id_product' => $id_product, 'date_equipment' => $date])->row_array();

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
        $this->db->select('no_batch, MAX(date_equipment) AS date_equipment');
        $this->db->group_by('tb_equipment_status.no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getBySpk($id_spk)
    {
        $this->db->order_by('tb_equipment_status.no_batch', 'DESC');
        $this->db->group_by('tb_equipment_status.no_batch');
        $result = $this->db->get_where('tb_equipment_status', ['id_spk' => $id_spk])->result_array();

        return $result;
    }

    public function getByFilter($batch = null, $date = null, $prd = null)
    {
        $this->db->select('tb_equipment_status.no_batch, date_equipment, id_spk');
        $this->db->join('tb_timbang', 'tb_equipment_status.no_batch = tb_timbang.no_batch');
        $this->db->group_by('tb_equipment_status.no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        if ($batch) {
            $this->db->where('tb_equipment_status.no_batch', $batch);
        }
        if ($date) {
            $this->db->where('tb_equipment_status.date_equipment', $date);
        }
        if ($prd) {
            $this->db->where('tb_timbang.kode_product', $prd);
        }
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getByDaterange($dateFrom, $dateTo)
    {
        $this->db->select('tb_equipment_status.no_batch, date_equipment, id_spk');
        $this->db->join('tb_timbang', 'tb_equipment_status.no_batch = tb_timbang.no_batch');
        $this->db->group_by('tb_equipment_status.no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $this->db->where('DATE(date_equipment) >= ', $dateFrom);
        $this->db->where('DATE(date_equipment) <= ', $dateTo);
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getByIdProduct($id_product)
    {
        $this->db->select('tb_equipment_status.no_batch, date_equipment, tb_equipment_status.id_spk');
        $this->db->join('tb_timbang', 'tb_equipment_status.no_batch = tb_timbang.no_batch');
        $this->db->join('tb_spk', 'tb_spk.id_spk = tb_equipment_status.id_spk');
        $this->db->group_by('tb_equipment_status.no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $this->db->where('tb_spk.id_product', $id_product);
        $this->db->where('tb_equipment_status.is_estop', 0);
        $result = $this->db->get('tb_equipment_status')->result_array();

        return $result;
    }

    public function getByBatch($no_batch)
    {
        $this->db->select('no_batch');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch])->row_array();

        return $result;
    }

    public function getEquipmentByBatch($no_batch)
    {
        $this->db->select('no_batch, name_equipment');
        $this->db->group_by('tb_equipment_status.name_equipment');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch])->result_array();

        return $result;
    }

    public function getEquipmentNoBahan($no_batch, $exceptName)
    {
        $this->db->select('no_batch, name_equipment');
        $this->db->where_not_in('name_equipment', $exceptName);
        $this->db->group_by('tb_equipment_status.name_equipment');
        $this->db->order_by('tb_equipment_status.created_at', 'DESC');
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch])->result_array();

        return $result;
    }

    public function getEquipmentOn($no_batch, $name)
    {
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch, 'name_equipment' => $name, 'status_equipment' => 'ON'])->row_array();

        return $result;
    }

    public function getFirstPenimbanganOn($no_batch)
    {
        $this->db->select('MIN(id_equipment_status) AS id_equipment_status');
        $this->db->where('no_batch', $no_batch);
        $this->db->where('status_equipment', 'ON');
        $this->db->like('name_equipment', 'PENIMBANGAN');
        $result = $this->db->get('tb_equipment_status')->row_array();

        return $result;
    }

    public function getLastPenimbanganOff($no_batch)
    {
        $this->db->select('MAX(id_equipment_status) AS id_equipment_status');
        $this->db->where('no_batch', $no_batch);
        $this->db->like('name_equipment', 'PENIMBANGAN');
        $result = $this->db->get('tb_equipment_status')->row_array();

        return $result;
    }

    public function getEquipmentOff($no_batch, $name)
    {
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch, 'name_equipment' => $name, 'status_equipment' => 'OFF'])->row_array();

        return $result;
    }

    public function checkEquipmentIfExist($no_batch, $status_equipment, $name_equipment, $id_spk)
    {
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch, 'status_equipment' => $status_equipment, 'name_equipment' => $name_equipment, 'id_spk' => $id_spk])->row_array();

        return $result;
    }

    public function checkEquipmentIfExistBatchOnly($no_batch, $status_equipment, $name_equipment)
    {
        $result = $this->db->get_where('tb_equipment_status', ['no_batch' => $no_batch, 'status_equipment' => $status_equipment, 'name_equipment' => $name_equipment])->row_array();

        return $result;
    }

    public function createFromArray($arrayEquipmentStatus)
    {
        $query = $this->db->insert('tb_equipment_status', $arrayEquipmentStatus);

        if ($query) {
            return true;
        } else {
            return false;
        }
    }
}
