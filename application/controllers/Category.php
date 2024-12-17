<?php

class Category extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $result = [
                'code' => 400,
                'status' => 'failed',
                'msg' => 'Method not found',
                'detail' => json_decode($response, true)
            ];

            $this->output->set_output(json_encode($result));
        } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {

            $accurate = $this->MAccurate->get();

            $token = $accurate['api_token'];
            $signature_secret = $accurate['signature_secret'];
            $timestamp = date("d/m/Y H:i:s");

            $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item-category/list.do?fields=id,name,no',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'X-Api-Timestamp: ' . $timestamp,
                    'X-Api-Signature: ' . $hash,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            $responseArray = json_decode($response, true);

            curl_close($curl);

            $items = $responseArray['d'];

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data found',
                'detail' => $items
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
