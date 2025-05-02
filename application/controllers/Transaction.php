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
                    'status' => 'failed',
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
                        'status' => 'failed',
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
                    'status' => 'failed',
                    'msg' => 'Tansaction not found',
                ];

                return $this->output->set_output(json_encode($response));
            }

            $id_transaction = $transaction['id_transaction'];

            $transactionDetails = $this->MTransactiondetail->getByFilter($id_transaction, 'all');

            $transactionDetailArray = array();

            foreach ($transactionDetails as $transactionDetail) {
                $spk = $this->MSpk->getById($transactionDetail['id_spk']);

                $excecutedBatch = $this->MEquipmentStatus->getBySpk($spk['id_spk']);

                $currentBatch = $excecutedBatch == null ? "0" : $excecutedBatch[0];

                $spk['excecuted_batch'] = count($excecutedBatch) . "";
                $spk['current_batch'] = $currentBatch['no_batch'];

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

        if ($checkRunningTrans != null) {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Other transaction still running',
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $save = true;

            if ($save) {
                $getDetail = $this->MTransactiondetail->getRowByStatus($id_transaction, 'PENDING');

                if ($getDetail) {
                    $id_transaction_detail = $getDetail['id_transaction_detail'];
                    $id_spk = $getDetail['id_spk'];

                    $transData = [
                        'status_transaction' => 'RUNNING',
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];

                    $save = $this->MTransaction->updateFromArray($id_transaction, $transData);

                    $detailTransData = [
                        'status_transaction_detail' => 'RUNNING',
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];

                    $save = $this->MTransactiondetail->updateFromArray($id_transaction_detail, $detailTransData);

                    if ($save) {
                        $spkData = [
                            'status_spk' => 'running'
                        ];

                        $save = $this->MSpk->updateFromArray($id_spk, $spkData);

                        $response = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Success running item',
                        ];

                        return $this->output->set_output(json_encode($response));
                    } else {
                        $response = [
                            'code' => 401,
                            'status' => 'failed',
                            'msg' => 'Fail start running transaction detail',
                        ];

                        return $this->output->set_output(json_encode($response));
                    }
                } else {

                    $response = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'No item left, transaction complete',
                    ];

                    return $this->output->set_output(json_encode($response));
                }
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Fail start running transaction',
                ];

                return $this->output->set_output(json_encode($response));
            }
        }
    }

    public function stop()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if (isset($post['id_transaction'])) {
            // Stop all
            $getTrans = $this->MTransaction->getById($post['id_transaction']);
            if (!$getTrans) {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Transaction not found',
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $transData = [
                    'status_transaction' => 'NOT_COMPLETE',
                    'updated_at' => date("Y-m-d H:i:s"),
                ];

                $save = $this->MTransaction->updateFromArray($getTrans['id_transaction'], $transData);

                if (!$save) {
                    $response = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Failed stopping trans',
                    ];

                    return $this->output->set_output(json_encode($response));
                } else {
                    $getTransDetail = $this->MTransactiondetail->getRowByStatus($getTrans['id_transaction'], 'RUNNING');

                    if (!$getTransDetail) {
                        $response = [
                            'code' => 401,
                            'status' => 'failed',
                            'msg' => 'No running transaction item',
                        ];

                        return $this->output->set_output(json_encode($response));
                    } else {
                        $id_spk = $getTransDetail['id_spk'];

                        $transDetailData = [
                            'status_transaction_detail' => 'STOPPED',
                            'updated_at' => date("Y-m-d H:i:s"),
                        ];

                        $save = $this->MTransactiondetail->updateFromArray($getTransDetail['id_transaction_detail'], $transDetailData);

                        if (!$save) {
                            $response = [
                                'code' => 401,
                                'status' => 'failed',
                                'msg' => 'Failed stopping transaction item',
                            ];

                            return $this->output->set_output(json_encode($response));
                        } else {
                            $spkData = [
                                'status_spk' => 'stopped'
                            ];

                            $save = $this->MSpk->updateFromArray($id_spk, $spkData);

                            $response = [
                                'code' => 200,
                                'status' => 'ok',
                                'msg' => 'Success stopping all transaction',
                            ];

                            return $this->output->set_output(json_encode($response));
                        }
                    }
                }
            }
        } else if (isset($post['id_transaction_detail'])) {
            // Stop detail
            $getTransDetail = $this->MTransactiondetail->getById($post['id_transaction_detail']);
            $id_transaction = $getTransDetail['id_transaction'];

            if (!$getTransDetail) {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Transaction item not found',
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $id_spk = $getTransDetail['id_spk'];

                $transDetailData = [
                    'status_transaction_detail' => 'STOPPED',
                    'updated_at' => date("Y-m-d"),
                ];

                $save = $this->MTransactiondetail->updateFromArray($post['id_transaction_detail'], $transDetailData);

                if (!$save) {
                    $response = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Failed stopping transaction',
                    ];

                    return $this->output->set_output(json_encode($response));
                } else {
                    $spkData = [
                        'status_spk' => 'stopped'
                    ];

                    $save = $this->MSpk->updateFromArray($id_spk, $spkData);

                    $getPendingTrans = $this->MTransactiondetail->getRowByStatus($id_transaction, 'PENDING');

                    if (!$getPendingTrans) {
                        $transData = [
                            'status_transaction' => 'NOT_COMPLETE',
                            'updated_at' => date("Y-m-d H:i:s"),
                        ];

                        $save = $this->MTransaction->updateFromArray($id_transaction, $transData);

                        $response = [
                            'code' => 200,
                            'status' => 'ok',
                            'msg' => 'Transaction item stopped, No other item to run',
                        ];

                        return $this->output->set_output(json_encode($response));
                    } else {
                        $transDetailData = [
                            'status_transaction_detail' => 'RUNNING',
                            'updated_at' => date("Y-m-d H:i:s"),
                        ];

                        $save = $this->MTransactiondetail->updateFromArray($getPendingTrans['id_transaction_detail'], $transDetailData);

                        if (!$save) {
                            $response = [
                                'code' => 401,
                                'status' => 'failed',
                                'msg' => 'Transaction stopped, but failed to run another item',
                            ];

                            return $this->output->set_output(json_encode($response));
                        } else {
                            $spkData = [
                                'status_spk' => 'running'
                            ];

                            $save = $this->MSpk->updateFromArray($id_spk, $spkData);

                            $response = [
                                'code' => 200,
                                'status' => 'ok',
                                'msg' => 'Success stopping transaction item, continue to other item',
                            ];

                            return $this->output->set_output(json_encode($response));
                        }
                    }
                }
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Please choose between id_transaction and id_transaction detail to be filled in',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    public function done()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $id_spk = $post['id_spk'];
        $status = $post['status'];
        $id_transaction_detail = $post['id_trans'];

        $getTransDetail = $this->MTransactiondetail->getById($id_transaction_detail);

        if (!$getTransDetail) {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Transaction not found',
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $getSpk = $this->MSpk->getById($id_spk);

            if (!$getSpk) {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'SPK not found',
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $jml_batch = $getSpk['jml_batch'];
                $getBatch = $this->MEquipmentStatus->getMixerOn($id_spk);
                $countBatch = count($getBatch);

                // echo $countBatch;
                // die;

                if ($countBatch == $jml_batch) {
                    $transDetailData = [
                        'status_transaction_detail' => 'DONE',
                        'updated_at' => date("Y-m-d H:i:s"),
                    ];

                    $this->MTransactiondetail->updateFromArray($id_transaction_detail, $transDetailData);

                    $spkData = [
                        'status_spk' => 'done',
                    ];

                    $this->MSpk->updateFromArray($id_spk, $spkData);

                    $id_transaction = $getTransDetail['id_transaction'];

                    $getPendingTrans = $this->MTransactiondetail->getRowByStatus($id_transaction, 'PENDING');

                    $getCountDetail = $this->MTransactiondetail->getByFilter($id_transaction, 'all');
                    $countDetail = count($getCountDetail);

                    $getDoneDetail = $this->MTransactiondetail->getByFilter($id_transaction, 'DONE');
                    $countDone = count($getDoneDetail);

                    // echo $countDetail;
                    // die;

                    if (!$getPendingTrans) {
                        $getTrans = $this->MTransaction->getById($id_transaction);

                        if ($countDone == $countDetail) {
                            $transData = [
                                'status_transaction' => 'COMPLETE',
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];

                            $save = $this->MTransaction->updateFromArray($id_transaction, $transData);

                            $response = [
                                'code' => 200,
                                'status' => 'ok',
                                'msg' => 'Transaction success, complete',
                            ];

                            return $this->output->set_output(json_encode($response));
                        } else {
                            $transData = [
                                'status_transaction' => 'NOT_COMPLETE',
                                'updated_at' => date('Y-m-d H:i:s'),
                            ];

                            $save = $this->MTransaction->updateFromArray($id_transaction, $transData);

                            $response = [
                                'code' => 200,
                                'status' => 'ok',
                                'msg' => 'Transaction success, not_complete',
                            ];

                            return $this->output->set_output(json_encode($response));
                        }
                    } else {
                        $transDetailData = [
                            'status_transaction_detail' => 'RUNNING',
                            'updated_at' => date("Y-m-d H:i:s"),
                        ];

                        $save = $this->MTransactiondetail->updateFromArray($getPendingTrans['id_transaction_detail'], $transDetailData);

                        if (!$save) {
                            $response = [
                                'code' => 401,
                                'status' => 'failed',
                                'msg' => 'Transaction stopped, but failed to run another item',
                            ];

                            return $this->output->set_output(json_encode($response));
                        } else {
                            $spkData = [
                                'status_spk' => 'running'
                            ];

                            $save = $this->MSpk->updateFromArray($getPendingTrans['id_spk'], $spkData);

                            $response = [
                                'code' => 200,
                                'status' => 'ok',
                                'msg' => 'Success continue to other item',
                            ];

                            return $this->output->set_output(json_encode($response));
                        }
                    }
                } else if ($countBatch < $jml_batch) {
                    $response = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Batch not complete',
                    ];

                    return $this->output->set_output(json_encode($response));
                }
            }
        }
    }


    public function deleteDetail($id_transaction_detail)
    {
        $this->output->set_content_type('application/json');

        $checkTransDetail = $this->MTransactiondetail->getById($id_transaction_detail);

        if ($checkTransDetail['status_transaction_detail'] == 'RUNNING') {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Cannot delete running transaction',
            ];

            return $this->output->set_output(json_encode($response));
        } else if ($checkTransDetail['status_transaction_detail'] == 'DONE') {
            $response = [
                'code' => 401,
                'status' => 'failed',
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
                    'status' => 'failed',
                    'msg' => 'Fail delete detail'
                ];

                return $this->output->set_output(json_encode($response));
            }
        }
    }
}
