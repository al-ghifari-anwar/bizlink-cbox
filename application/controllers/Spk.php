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
            // Semen
            $target_semen = 0;
            $fine_semen = 0;
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
                if (str_contains(strtolower($formula['name_material']), 'semen')) {
                    $target_semen = $formula['target_formula'];
                    $fine_semen = $formula['fine_formula'];
                } else if (str_contains(strtolower($formula['name_material']), 'kapur')) {
                    $target_kapur = $formula['target_formula'];
                    $fine_kapur = $formula['fine_formula'];
                } else if (str_contains(strtolower($formula['name_material']), 'pasir')) {
                    $target_pasir = $formula['target_formula'];
                    $fine_pasir = $formula['fine_formula'];
                } else if (str_contains(strtolower($formula['name_material']), 'additif')) {
                    $target_additif = $formula['target_formula'];
                    $fine_additif = $formula['fine_formula'];
                }
            }

            $response = [
                'spk' => $getSpk,
                'product' => $getProduct,
                'formula' => [
                    'target_semen' => $target_semen,
                    'fine_semen' => $fine_semen,
                    'target_kapur' => $target_kapur,
                    'fine_kapur' => $fine_kapur,
                    'target_pasir' => $target_pasir,
                    'fine_pasir' => $fine_pasir,
                    'target_additif' => $target_additif,
                    'fine_additif' => $fine_additif
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
