<?php

class MTransactiondetail extends CI_Model
{
    public function getById($id_transaction_detail)
    {
        $result = $this->db->get_where('tb_transaction_detail', ['id_transaction_detail' => $id_transaction_detail])->row_array();

        return $result;
    }

    public function getByFilter($id_transaction, $status_transaction)
    {
        $this->db->order_by('updated_at', 'DESC');
        $this->db->where('id_transaction', $id_transaction);
        if ($status_transaction != 'all') {
            $this->db->where('status_transaction_detail', $status_transaction);
        }
        $result = $this->db->get('tb_transaction_detail')->result_array();

        return $result;
    }

    public function getRowByStatus($id_transaction, $status_transaction)
    {
        $this->db->order_by('updated_at', 'DESC');
        $this->db->where('id_transaction', $id_transaction);
        if ($status_transaction != 'all') {
            $this->db->where('status_transaction_detail', $status_transaction);
        }
        $result = $this->db->get('tb_transaction_detail')->row_array();

        return $result;
    }

    public function createFromArray($transactionDetailData)
    {
        $save = $this->db->insert('tb_transaction_detail', $transactionDetailData);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }

    public function updateFromArray($id_transaction_detail, $transactionDetailData)
    {
        $save = $this->db->update('tb_transaction_detail', $transactionDetailData, ['id_transaction_detail' => $id_transaction_detail]);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }

    public function delete($id_transaction_detail)
    {
        $save = $this->db->delete('tb_transaction_detail', ['id_transaction_detail' => $id_transaction_detail]);

        if ($save) {
            return true;
        } else {
            return false;
        }
    }
}
