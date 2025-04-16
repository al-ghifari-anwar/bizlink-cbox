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

            if ($transaction == null) {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Tansaction not found',
                ];

                return $this->output->set_output(json_encode($response));
            }

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

    public function start()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $id_transaction = $post['id_transaction'];

        $checkRunningTrans = $this->MTransaction->getRowByStatus('RUNNING');

        if ($checkRunningTrans == null) {
            $transData = [
                'status_transaction' => 'RUNNING',
                'updated_at' => date("Y-m-d H:i:s"),
            ];

            $save = $this->MTransaction->updateFromArray($id_transaction, $transData);

            if ($save) {
                $getDetail = $this->MTransactiondetail->getRowByStatus($id_transaction, 'PENDING');

                if ($getDetail) {
                    $id_transaction_detail = $getDetail['id_transaction_detail'];

                    $detailTransData = [
                        'status_transaction_detail' => 'RUNNING',
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];

                    $save = $this->MTransactiondetail->updateFromArray($id_transaction_detail, $detailTransData);

                    if ($save) {
                        $response = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Success running first item',
                        ];

                        return $this->output->set_output(json_encode($response));
                    } else {
                        $response = [
                            'code' => 401,
                            'status' => 'ok',
                            'msg' => 'Fail start running transaction detail',
                        ];

                        return $this->output->set_output(json_encode($response));
                    }
                } else {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'No item left, transaction complete',
                    ];

                    return $this->output->set_output(json_encode($response));
                }
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Fail start running transaction',
                ];

                return $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Other transaction still running',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    public function deleteDetail($id_transaction_detail)
    {
        $this->output->set_content_type('application/json');

        $checkTransDetail = $this->MTransactiondetail->getById($id_transaction_detail);

        if ($checkTransDetail['status_transaction_detail'] == 'RUNNING') {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Cannot delete running transaction',
            ];

            return $this->output->set_output(json_encode($response));
        } else if ($checkTransDetail['status_transaction_detail'] == 'DONE') {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Cannot delete finished transaction',
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $delete = $this->MTransactiondetail->delete($id_transaction_detail);

            if ($delete) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Success delete detail'
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Fail delete detail'
                ];

                return $this->output->set_output(json_encode($response));
            }
        }
    }
}
