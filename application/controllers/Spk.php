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
    }

    public function api()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['source'])) {
                $source = $_GET['source'];

                $accurate = $this->MAccurate->get();

                $token = $accurate['api_token'];
                $signature_secret = $accurate['signature_secret'];
                $timestamp = date("d/m/Y H:i:s");

                $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item/list.do?fields=id,name,no&filter.itemCategoryId.op=EQUAL&filter.itemCategoryId.val=150',
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
            } else {
                $result = $this->MProduct->get();

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'data' => $result
                ];

                $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->MProduct->create();

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

    public function getToday()
    {
        $this->output->set_content_type('application/json');

        $getSpk = $this->MSpk->getToday();

        $getProduct = $this->MProduct->getById($getSpk['id_product']);
        // $query_p = $this->db->last_query();

        $getFormula = $this->MFormula->getByProductId($getSpk['id_product']);

        if ($getSpk) {
            // Semen Grey
            $target_semen_grey = 0;
            $fine_semen_grey = 0;
            // Semen Putih
            $target_semen_putih = 0;
            $fine_semen_putih = 0;
            // Kapur
            $target_kapur = 0;
            $fine_kapur = 0;
            // Pasir
            $target_pasir = 0;
            $fine_pasir = 0;
            // Additif
            $target_additif = 0;
            $fine_additif = 0;
            foreach ($getFormula as $formula) {
                if ($formula['kode_material'] == '1001') {
                    $target_semen_grey = $formula['target_formula'];
                    $fine_semen_grey = $formula['fine_formula'];
                    $kode_semen_grey = $formula['kode_material'];
                } else if ($formula['kode_material'] == '1004') {
                    $target_semen_putih = $formula['target_formula'];
                    $fine_semen_putih = $formula['fine_formula'];
                    $kode_semen_putih = $formula['kode_material'];
                } else if ($formula['kode_material'] == '1002') {
                    $target_kapur = $formula['target_formula'];
                    $fine_kapur = $formula['fine_formula'];
                    $kode_kapur = $formula['kode_material'];
                } else if ($formula['kode_material'] == '1003') {
                    $target_pasir = $formula['target_formula'];
                    $fine_pasir = $formula['fine_formula'];
                    $kode_pasir = $formula['kode_material'];
                } else if ($formula['kode_material'] == 'PREMIX-THINBED') {
                    $target_additif = $formula['target_formula'];
                    $fine_additif = $formula['fine_formula'];
                    $kode_additif = $formula['kode_material'];
                }
            }

            $response = [
                'spk' => $getSpk,
                'product' => $getProduct,
                'formula' => [
                    'target_semen_grey' => $target_semen_grey,
                    'fine_semen_grey' => $fine_semen_grey,
                    'kode_semen_grey' => $kode_semen_grey,
                    'target_semen_putih' => $target_semen_putih,
                    'fine_semen_putih' => $fine_semen_putih,
                    'kode_semen_putih' => $kode_semen_putih,
                    'target_kapur' => $target_kapur,
                    'fine_kapur' => $fine_kapur,
                    'kode_kapur' => $kode_kapur,
                    'target_pasir' => $target_pasir,
                    'fine_pasir' => $fine_pasir,
                    'kode_pasir' => $kode_pasir,
                    'target_additif' => $target_additif,
                    'fine_additif' => $fine_additif,
                    'kode_additif' => $kode_additif
                ]
            ];

            $this->output->set_output(json_encode($response));
        } else {
            $response = [
                'code' => 400,
                'status' => 'ok',
                'msg' => 'Spk tidak ditemukan'
            ];

            $this->output->set_output(json_encode($response));
        }
    }
}
