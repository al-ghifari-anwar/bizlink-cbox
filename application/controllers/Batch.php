<?php

class Batch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
        $this->load->model('MProduct');
        $this->load->model('MFormula');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $batch = isset($_GET['batch']) ? $_GET['batch'] : null;
            $date = isset($_GET['date']) ? $_GET['date'] : null;
            $prd = isset($_GET['prd']) ? $_GET['prd'] : null;

            $completeBatch = array();

            $batchs = $this->MEquipmentStatus->getByFilter($batch, $date, $prd);

            foreach ($batchs as $batch) {
                $timbang = $this->MTimbang->getPrdByBatch($batch['no_batch']);
                $kode_product = $timbang != null ? $timbang['kode_product'] : '';
                $product = $this->MProduct->getByKode($kode_product);
                // echo json_encode($timbang);
                // die;
                $batch['product'] = $product;
                // Push to new array
                $completeBatch[] = $batch;
            }

            $result = $completeBatch;

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    public function detail($no_batch)
    {
        $this->output->set_content_type('application/json');

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if ($no_batch != null) {
                $resultBatch = $this->MEquipmentStatus->getByBatch($no_batch);

                $resultEquipment = $this->MEquipmentStatus->getEquipmentByBatch($no_batch);

                if ($resultEquipment == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Data Equipment Tidak Ditemukan'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getTimbang = $this->MTimbang->getByBatch($no_batch);

                if ($getTimbang == null && $resultEquipment != null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Batch ' . $no_batch . ' tidak memiliki data timbang, tetapi memiliki data equipment. Kemungkinan terjadi karna ketidakcocokan no batch'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getKodeProduct = $this->MTimbang->getPrdByBatch($no_batch);
                $kode_product = $getKodeProduct['kode_product'];

                $getProduct = $this->MProduct->getByKode($kode_product);

                if ($getProduct == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Produk dengan kode ' . $kode_product . ' tidak dapat ditemukan dalam database.'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $rekapEquipment = array();

                $getTimbangWithResep = array();
                // Inject Resep
                foreach ($getTimbang as $timbang) {
                    $kode_material = $timbang['kode_bahan'];
                    $formulaMaterial = $this->MFormula->getByProductIdAndMaterial($getProduct['id_product'], $kode_material);
                    $timbang['formula'] = $formulaMaterial;
                    $getTimbangWithResep[] = $timbang;
                }

                foreach ($resultEquipment as $equipment) {
                    $name_equipment = $equipment['name_equipment'];

                    $getEquipmentOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);
                    if ($getEquipmentOn == null) {
                        $response = [
                            'code' => 401,
                            'status' => 'ok',
                            'msg' => 'Equipment ' . $name_equipment . ' tidak memiliki data ON atau status ketika euipment start'
                        ];

                        return $this->output->set_output(json_encode($response));
                    }
                    $getEquipmentOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);
                    $timeOn = $getEquipmentOn['date_equipment'] . " " . $getEquipmentOn['time_equipment'];


                    if ($getEquipmentOff != null) {
                        $timeOff = $getEquipmentOff['date_equipment'] . " " . $getEquipmentOff['time_equipment'];

                        $date1 = new DateTime($timeOn);
                        $date2 = new DateTime($timeOff);
                        $diference  = $date2->diff($date1);

                        $interval = $this->format_interval($diference);

                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => $timeOff,
                            'time_elapsed' => $interval,
                            'desc' => 'finished'
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    } else {
                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => "0",
                            'time_elapsed' => "0",
                            'desc' => 'running'
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    }
                }

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'product' => $kode_product,
                    'dataEquipment' => $rekapEquipment,
                    'dataTimbang' => $getTimbangWithResep,
                    'dataProduct' => $getProduct,
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'No batch cannot be null'
                ];

                return $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    function format_interval(DateInterval $interval)
    {
        $result = "";
        if ($interval->y) {
            $result .= $interval->format("%y yr ");
        }
        if ($interval->m) {
            $result .= $interval->format("%m mnth ");
        }
        if ($interval->d) {
            $result .= $interval->format("%d days ");
        }
        if ($interval->h) {
            $result .= $interval->format("%h hrs ");
        }
        if ($interval->i) {
            $result .= $interval->format("%i mnt ");
        }
        if ($interval->s) {
            $result .= $interval->format("%s scnd ");
        }

        return $result;
    }
}
