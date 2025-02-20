<?php

class Equipmentstatus extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['name'])) {
                $result = $this->MEquipmentStatus->getByName($_GET['name']);

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $result = $this->MEquipmentStatus->get();

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result
                ];

                return $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();
            // msg . payload = [msg . payload . Nobatch, msg . payload . Status, msg . payload . Nama, msg . payload . Tanggal, msg . payload . Jam, msg . payload . id_spk];
            // msg . topic = "INSERT INTO tb_equipment_status (`no_batch`, `status_equipment`, `name_equipment`, `date_equipment`, `time_equipment`,`id_spk`) VALUES (?, ?, ?, ?, ?,?)";
            // return msg;
            $no_batch = $post['no_batch'];
            $status_equipment = $post['status_equipment'];
            $name_equipment = $post['name_equipment'];
            $date_equipment = $post['date_equipment'];
            $time_equipment = $post['time_equipment'];
            $id_spk = $post['id_spk'];

            $checkEquipment = $this->MEquipmentStatus->checkEquipmentIfExist($no_batch, $status_equipment, $name_equipment, $id_spk);

            if ($checkEquipment) {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Equipment already exist',
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $arrayEquipmentStatus = [
                    'no_batch' => $no_batch,
                    'status_equipment' => $status_equipment,
                    'name_equipment' => $name_equipment,
                    'date_equipment' => date("Y-m-d", strtotime($date_equipment)),
                    'time_equipment' => date("H:i:s", strtotime($time_equipment)),
                    'id_spk' => $id_spk
                ];

                $save = $this->MEquipmentStatus->createFromArray($arrayEquipmentStatus);

                if ($save) {
                    $response = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Equipment saved',
                    ];

                    return $this->output->set_output(json_encode($response));
                } else {
                    $response = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Failed to save equipment',
                    ];

                    return $this->output->set_output(json_encode($response));
                }
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
}
