<?php

class MTransaction extends CI_Model
{
    public function getById($id_transaction)
    {
        $this->db->where('id_transaction', $id_transaction);
        $result = $this->db->get('tb_transaction')->row_array();

        return $result;
    }

    public function getByFilter($date_transaction, $status_transaction)
    {
        $this->db->order_by('date_transaction', 'DESC');
        $this->db->where('DATE(date_transaction)', $date_transaction);
        if ($status_transaction != 'all') {
            $this->db->where('status_transaction', $status_transaction);
        }
        $result = $this->db->get('tb_transaction')->result_array();

        return $result;
    }

    public function createFromArray($transactionData)
    {
        $save = $this->db->insert('tb_transaction', $transactionData);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }

    public function updateFromArray($id_transaction, $transactionData)
    {
        $save = $this->db->update('tb_transaction', $transactionData, ['id_transaction' => $id_transaction]);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }
}
