<?php

class Token extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        $accurate = $this->MAccurate->get();

        $token = $accurate['api_token'];
        $signature_secret = $accurate['signature_secret'];
        $timestamp = date("d/m/Y H:i:s");
        // $signature_secret = "31d49b3dc632614495ff8071e5be44a1";
        // $timestamp = "02/11/2023 09:01:01";

        $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://account.accurate.id/api/api-token.do',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $token,
                'X-Api-Timestamp: ' . $timestamp,
                'X-Api-Signature: ' . $hash
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // echo $response;

        $result = [
            'code' => 200,
            'status' => 'ok',
            'msg' => 'Data found',
            'detail' => json_decode($response, true),
            'auth' => $accurate,
            'hash' => $hash,
            'timestamp' => $timestamp
        ];

        $this->output->set_output(json_encode($result));
    }
}
