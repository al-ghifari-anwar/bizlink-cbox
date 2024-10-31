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

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['name'])) {
                $result = $this->MEquipmentStatus->getByName($_GET['name']);

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $result = $this->MEquipmentStatus->get();

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result
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
}
