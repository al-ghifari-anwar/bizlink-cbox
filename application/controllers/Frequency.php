<?php

class Frequency extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // 
            $this->db->order_by('id_frequency', 'DESC');
            $frequencys = $this->db->get('tb_frequency')->row_array();

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $frequencys
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

    public function getForCbox()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // 
            $this->db->order_by('id_frequency', 'DESC');
            $frequencys = $this->db->get('tb_frequency')->row_array();

            $frequencyData = [
                'semen_high' => $frequencys['semen_high'] * 100,
                'semen_low' => $frequencys['semen_low'] * 100,
                'kapur_high' => $frequencys['kapur_high'] * 100,
                'kapur_low' => $frequencys['kapur_low'] * 100,
                'pasir_kasar_high' => $frequencys['pasir_kasar_high'] * 100,
                'pasir_kasar_low' => $frequencys['pasir_kasar_low'] * 100,
                'pasir_halus_high' => $frequencys['pasir_halus_high'] * 100,
                'pasir_halus_low' => $frequencys['pasir_halus_low'] * 100,
                'semen_putih_high' => $frequencys['semen_putih_high'] * 100,
                'semen_putih_low' => $frequencys['semen_putih_low'] * 100,
            ];

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Success',
                'data' => $frequencyData
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

    public function save()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $frquencyData = [
                'semen_high' => $post['semen_high'],
                'semen_low' => $post['semen_low'],
                'kapur_high' => $post['kapur_high'],
                'kapur_low' => $post['kapur_low'],
                'pasir_kasar_high' => $post['pasir_kasar_high'],
                'pasir_kasar_low' => $post['pasir_kasar_low'],
                'pasir_halus_high' => $post['pasir_halus_high'],
                'pasir_halus_low' => $post['pasir_halus_low'],
                'semen_putih_high' => $post['semen_putih_high'],
                'semen_putih_low' => $post['semen_putih_low'],
            ];

            $save = $this->db->insert('tb_frequency', $frquencyData);

            if ($save) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Berhasil',
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Terjadi kesalahan',
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
}
