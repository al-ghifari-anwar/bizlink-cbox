<?php

class Batch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $result = $this->MEquipmentStatus->getAllBatch();

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

                $rekapEquipment = array();

                foreach ($resultEquipment as $equipment) {
                    $name_equipment = $equipment['name_equipment'];

                    $getEquipmentOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);
                    $getEquipmentOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);
                    $timeOn = $getEquipmentOn['date_equipment'] . " " . $getEquipmentOn['time_equipment'];

                    $getTimbang = $this->MTimbang->getByBatch($no_batch);

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
                            'desc' => 'finished',
                            'hasil_timbang' => $getTimbang
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    } else {
                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => "0",
                            'time_elapsed' => "0",
                            'desc' => 'running',
                            'hasil_timbang' => $getTimbang
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    }
                }

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $rekapEquipment
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
            $result .= $interval->format("%y years ");
        }
        if ($interval->m) {
            $result .= $interval->format("%m months ");
        }
        if ($interval->d) {
            $result .= $interval->format("%d days ");
        }
        if ($interval->h) {
            $result .= $interval->format("%h hours ");
        }
        if ($interval->i) {
            $result .= $interval->format("%i minutes ");
        }
        if ($interval->s) {
            $result .= $interval->format("%s seconds ");
        }

        return $result;
    }
}
