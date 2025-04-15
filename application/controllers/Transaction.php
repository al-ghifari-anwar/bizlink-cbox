<?php

class Transaction extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
        $this->load->model('MProduct');
        $this->load->model('MSpk');
        $this->load->model('MFormula');
        $this->load->model('MTransaction');
        $this->load->model('MTransactiondetail');
    }

    public function api()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $date_transaction = $_GET['date'];
            $status_transaction = $_GET['status'];

            $result = $this->MTransaction->getByFilter($date_transaction, $status_transaction);

            if ($result != null) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result,
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Transaction empty'
                ];

                return $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            if (isset($post['id_transaction'])) {
                $id_transaction = $post['id_transaction'];

                foreach (json_decode($post['list_spk'], true) as $listSpk) {
                    $transactionDetailData = [
                        'id_transaction' => $id_transaction,
                        'id_spk' => $listSpk,
                        'status_transaction_detail' => 'PENDING',
                    ];

                    $save = $this->MTransactiondetail->createFromArray($transactionDetailData);
                }

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success adding spk to transaction'
                ];

                return $this->output->set_output(json_encode($response));
            } else {

                $transactionData = [
                    'date_transaction' => date("Y-m-d H:i:s"),
                    'status_transaction' => 'PENDING',
                ];

                $save = $this->MTransaction->createFromArray($transactionData);

                if ($save) {
                    $id_transaction = $this->db->insert_id();

                    foreach (json_decode($post['list_spk'], true) as $listSpk) {
                        $transactionDetailData = [
                            'id_transaction' => $id_transaction,
                            'id_spk' => $listSpk,
                            'status_transaction_detail' => 'PENDING',
                        ];

                        $save = $this->MTransactiondetail->createFromArray($transactionDetailData);
                    }

                    $response = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Success transaction'
                    ];

                    return $this->output->set_output(json_encode($response));
                } else {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Failed creating transaction'
                    ];

                    return $this->output->set_output(json_encode($response));
                }
            }
            // $this->output->set_output(json_encode($arraySpk));
        }
    }

    public function api_by_id($id_transaction)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $transaction = $this->MTransaction->getById($id_transaction);
            $id_transaction = $transaction['id_transaction'];

            $transactionDetails = $this->MTransactiondetail->getByFilter($id_transaction, 'all');

            $transactionDetailArray = array();

            foreach ($transactionDetails as $transactionDetail) {
                $spk = $this->MSpk->getById($transactionDetail['id_spk']);

                $transactionDetail['spk'] = $spk;

                array_push($transactionDetailArray, $transactionDetail);
            }

            $transaction['detail'] = $transactionDetailArray;

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $transaction
            ];

            return $this->output->set_output(json_encode($response));
        }
    }
}
