<?php

class User extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
        $this->load->model('MUser');
    }

    public function api()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $result = $this->MUser->get();

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            $this->output->set_output(json_encode($response));
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->MUser->create();

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
        } else {
            $response = [
                'code' => 401,
                'status' => 'ok',
                'msg' => 'Method not found'
            ];

            $this->output->set_output(json_encode($response));
        }
    }

    public function api_by_id($id)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $result = $this->MUser->getById($id);

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            $this->output->set_output(json_encode($response));
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->MUser->update($id);

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
            $result = $this->MUser->destroy($id);

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
}
