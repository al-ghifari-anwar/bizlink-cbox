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
            $completeBatch = array();

            $batchs = $this->MEquipmentStatus->getAllBatch();

            foreach ($batchs as $batch) {
                $timbang = $this->MTimbang->getPrdByBatch($batch['no_batch']);
                // $product = $this->MProduct->getByKode($timbang['kode_product']);
                $batch['product'] = $timbang;
                // Push to new array
                $completeBatch = $batch;
            }

            $result = $completeBatch;

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            $this->output->set_output(json_encode($response));
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            $this->output->set_output(json_encode($response));
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

                $getTimbang = $this->MTimbang->getByBatch($no_batch);

                $getKodeProduct = $this->MTimbang->getPrdByBatch($no_batch);
                $kode_product = $getKodeProduct['kode_product'];

                $getProduct = $this->MProduct->getByKode($kode_product);

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

                $this->output->set_output(json_encode($response));
            } else {

                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'No batch cannot be null'
                ];

                $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            $this->output->set_output(json_encode($response));
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
