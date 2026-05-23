<?php

class Spk extends CI_Controller
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
            $date_spk = $_GET['date'];
            $status_spk = $_GET['status'];

            $result = $this->MSpk->getByFilter($date_spk, $status_spk);

            if ($result != null) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result,
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'SPK empty'
                ];

                $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            // if ($spk != null) {
            //     $response = [
            //         'code' => 401,
            //         'status' => 'ok',
            //         'msg' => 'Sudah ada spk di tanggal tersebut'
            //     ];

            //     $this->output->set_output(json_encode($response));
            // } else {
            $result = $this->MSpk->create();

            if ($result) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data created'
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Data not created',
                    'detail' => $this->db->error()
                ];

                $this->output->set_output(json_encode($response));
            }
            // }
        } else {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Method not found'
            ];

            $this->output->set_output(json_encode($response));
        }
    }

    public function api_by_id($id_spk)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $result = $this->MSpk->getById($id_spk);

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            $this->output->set_output(json_encode($response));
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->MSpk->update($id_spk);

            if ($result) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data updated'
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Data not updated',
                    'detail' => $this->db->error()
                ];

                $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $result = $this->MSpk->destroy($id_spk);

            if ($result) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data deleted'
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Data not deleted',
                    'detail' => $this->db->error()
                ];

                $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Method not found'
            ];

            $this->output->set_output(json_encode($response));
        }
    }

    public function getAvailable()
    {
        $this->output->set_content_type('application/json');

        $spks = $this->MSpk->get();

        $spkArray = array();

        foreach ($spks as $spk) {
            $id_spk = $spk['id_spk'];
            $checkSpk = $this->db->get_where('tb_transaction_detail', ['id_spk' => $id_spk])->row_array();

            if ($checkSpk != null) {
                if ($spk['status_spk'] != 'done') {
                    $condTransaction = array('RUNNING', 'PENDING');
                    if (!in_array($checkSpk['status_transaction_detail'], $condTransaction)) {
                        $spk['status_transaction_detail'] = $checkSpk['status_transaction_detail'];
                        array_push($spkArray, $spk);
                    }
                }
            } else {
                if ($spk['status_spk'] != 'done') {
                    array_push($spkArray, $spk);
                }
            }
        }

        if ($spkArray) {
            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $spkArray
            ];

            $this->output->set_output(json_encode($response));
        } else {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Not found'
            ];

            $this->output->set_output(json_encode($response));
        }
    }

    public function getToday()
    {
        $this->output->set_content_type('application/json');

        $getTrans = $this->MTransaction->getRowByStatus('RUNNING');

        if ($getTrans) {
            $getTransDetail = $this->MTransactiondetail->getRowByStatus($getTrans['id_transaction'], 'RUNNING');
            if ($getTransDetail) {
                $getSpk = $this->MSpk->getById($getTransDetail['id_spk']);
                if ($getSpk) {
                    $countBatch = $this->MEquipmentStatus->getMixerOn($getSpk['id_spk']);
                    $getProduct = $this->MProduct->getById($getSpk['id_product']);
                    $getFormula = $this->MFormula->getByProductId($getSpk['id_product']);

                    $hasilBatch = count($countBatch);

                    $getSpk['jml_batch'] = $getSpk['status_spk'] == 'done' ? 0 : $getSpk['jml_batch'];

                    $getLastBatch = $this->MEquipmentStatus->getMixerOnForBatchingNumber($getProduct['id_product'], date("Y-m-d"));

                    if (!$getLastBatch) {
                        // Kode barang, tanggal, plant, bulan, tahun, nomor
                        $noBatchAwal = $getProduct['code_batch'] . date('d') . '2' . date('my') . str_pad('1', 6, '0', STR_PAD_LEFT);
                    } else {
                        $lastBatch = $getLastBatch['no_batch'];
                        $lastNumber = ltrim(substr($lastBatch, -6), '0') + 1;
                        $noBatchAwal = $getProduct['code_batch'] . date('d') . '2' . date('my') . str_pad($lastNumber, 6, '0', STR_PAD_LEFT);
                    }

                    // $batchSebelumnya = "";

                    // $getMixerOff = $this->MEquipmentStatus->getEquipmentOff($lastBatch, 'MIXER');

                    // if ($getMixerOff) {
                    //     $batchSebelumnya = $noBatchAwal;
                    // }

                    $getSpk['batchAwal'] = $noBatchAwal;
                    $getSpk['batchSebelumnya'] = $getLastBatch == null ? $noBatchAwal : $getLastBatch['no_batch'];
                    $getSpk['id_trans'] = $getTransDetail['id_transaction_detail'];

                    // Semen Grey
                    $target_semen_grey = 0;
                    $fine_semen_grey = 0;
                    $coarse_semen_grey = 0;
                    $kode_semen_grey = '';
                    $urutan_semen_grey = 0;
                    // Semen Putih
                    $target_semen_putih = 0;
                    $fine_semen_putih = 0;
                    $coarse_semen_putih = 0;
                    $kode_semen_putih = '';
                    $urutan_semen_putih = 0;
                    // Kapur
                    $target_kapur = 0;
                    $fine_kapur = 0;
                    $coarse_kapur = 0;
                    $kode_kapur = '';
                    $urutan_kapur = 0;
                    // Pasir Halus
                    $target_pasir_halus = 0;
                    $fine_pasir_halus = 0;
                    $coarse_pasir_halus = 0;
                    $kode_pasir_halus = '';
                    $urutan_pasir_halus = 0;
                    // Pasir Kasar
                    $target_pasir_kasar = 0;
                    $fine_pasir_kasar = 0;
                    $coarse_pasir_kasar = 0;
                    $kode_pasir_kasar = '';
                    $urutan_pasir_kasar = 0;
                    // Additif
                    $target_additif = 0;
                    $fine_additif = 0;
                    $coarse_additif = 0;
                    $kode_additif = '';
                    $urutan_additif = 0;
                    foreach ($getFormula as $formula) {
                        if ($formula['kode_material'] == '1001') {
                            $target_semen_grey = $formula['target_formula'] * 10;
                            $fine_semen_grey = $formula['fine_formula'] * 10;
                            $kode_semen_grey = $formula['kode_material'];
                            $coarse_semen_grey = $formula['coarse_formula'] * 10;
                            $urutan_semen_grey = $formula['urutan_formula'];
                        } else if ($formula['kode_material'] == '1004') {
                            $target_semen_putih = $formula['target_formula'] * 10;
                            $fine_semen_putih = $formula['fine_formula'] * 10;
                            $coarse_semen_putih = $formula['coarse_formula'] * 10;
                            $kode_semen_putih = $formula['kode_material'];
                            $urutan_semen_putih = $formula['urutan_formula'];
                        } else if ($formula['kode_material'] == '1002') {
                            $target_kapur = $formula['target_formula'] * 10;
                            $fine_kapur = $formula['fine_formula'] * 10;
                            $coarse_kapur = $formula['coarse_formula'] * 10;
                            $kode_kapur = $formula['kode_material'];
                            $urutan_kapur = $formula['urutan_formula'];
                        } else if ($formula['kode_material'] == '1003') {
                            $target_pasir_halus = $formula['target_formula'] * 10;
                            $fine_pasir_halus = $formula['fine_formula'] * 10;
                            $coarse_pasir_halus = $formula['coarse_formula'] * 10;
                            $kode_pasir_halus = $formula['kode_material'];
                            $urutan_pasir_halus = $formula['urutan_formula'];
                        } else if ($formula['kode_material'] == '100007') {
                            $target_pasir_kasar = $formula['target_formula'] * 10;
                            $fine_pasir_kasar = $formula['fine_formula'] * 10;
                            $coarse_pasir_kasar = $formula['coarse_formula'] * 10;
                            $kode_pasir_kasar = $formula['kode_material'];
                            $urutan_pasir_kasar = $formula['urutan_formula'];
                        } else if (str_contains($formula['name_material'], 'PREMIX') || str_contains($formula['name_material'], 'premix') || str_contains($formula['name_material'], 'Premix') || str_contains($formula['name_material'], 'ADTF') || str_contains($formula['name_material'], 'adtf') || str_contains($formula['name_material'], 'Aluminium Powder') || str_contains($formula['name_material'], 'TRIPERSE') || str_contains($formula['name_material'], 'ADDITIVE')) {
                            $target_additif = $formula['target_formula'] * 10;
                            $fine_additif = $formula['fine_formula'] * 10;
                            $coarse_additif = $formula['coarse_formula'] * 10;
                            $kode_additif = $formula['kode_material'];
                            $urutan_additif = $formula['urutan_formula'];
                        }
                    }

                    $this->db->order_by('created_at', 'DESC');
                    $resetBatch = $this->db->get('tb_reset_batch', 1)->row_array();

                    $dateReset = date("Y-m-d H:i:s", strtotime("+5 seconds", strtotime($resetBatch['created_at'])));
                    $dateNow = date("Y-m-d H:i:s");

                    if ($dateReset <= $dateNow) {
                        $response = [
                            'spk' => $getSpk,
                            'product' => $getProduct,
                            'formula' => [
                                'target_semen_grey' => $target_semen_grey,
                                'fine_semen_grey' => $fine_semen_grey,
                                'coarse_semen_grey' => $coarse_semen_grey,
                                'kode_semen_grey' => $kode_semen_grey,
                                'urutan_semen_grey' => $urutan_semen_grey,
                                'target_semen_putih' => $target_semen_putih,
                                'fine_semen_putih' => $fine_semen_putih,
                                'coarse_semen_putih' => $coarse_semen_putih,
                                'kode_semen_putih' => $kode_semen_putih,
                                'urutan_semen_putih' => $urutan_semen_putih,
                                'target_kapur' => $target_kapur,
                                'fine_kapur' => $fine_kapur,
                                'coarse_kapur' => $coarse_kapur,
                                'kode_kapur' => $kode_kapur,
                                'urutan_kapur' => $urutan_kapur,
                                'target_pasir_kasar' => $target_pasir_kasar,
                                'fine_pasir_kasar' => $fine_pasir_kasar,
                                'coarse_pasir_kasar' => $coarse_pasir_kasar,
                                'kode_pasir_kasar' => $kode_pasir_kasar,
                                'urutan_pasir_kasar' => $urutan_pasir_kasar,
                                'target_pasir_halus' => $target_pasir_halus,
                                'fine_pasir_halus' => $fine_pasir_halus,
                                'coarse_pasir_halus' => $coarse_pasir_halus,
                                'kode_pasir_halus' => $kode_pasir_halus,
                                'urutan_pasir_halus' => $urutan_pasir_halus,
                                'target_additif' => $target_additif,
                                'fine_additif' => $fine_additif,
                                'coarse_additif' => $coarse_additif,
                                'kode_additif' => $kode_additif,
                                'urutan_additif' => $urutan_additif,
                            ]
                        ];

                        $this->output->set_output(json_encode($response));
                    } else {
                        $response = [
                            'code' => 400,
                            'status' => 'failed',
                            'msg' => 'RESET'
                        ];

                        $this->output->set_output(json_encode($response));
                    }
                } else {
                    $response = [
                        'code' => 400,
                        'status' => 'failed',
                        'msg' => 'Spk tidak ditemukan'
                    ];

                    $this->output->set_output(json_encode($response));
                }
            } else {
                $response = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Transaction item not found'
                ];

                $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'No Transaction'
            ];

            $this->output->set_output(json_encode($response));
        }
    }
}
