<?php

class Timbang extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MFormula');
        $this->load->model('MProduct');
        $this->load->model('MQontak');
        $this->load->model('MTimbang');
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $accurate = $this->MAccurate->get();

        $no_batch = $post['no_batch'];
        $kode_product = $post['kode_product'];

        $product = $this->MProduct->getByKode($kode_product);
        $itemNo = $product['kode_product'];
        $formula = $this->MFormula->getByProductIdAndMaterial($product['id_product'], $itemNo);

        $jmlFormula = $this->MFormula->getJmlByProductId($product['id_product']);
        $jmlTimbang = $this->MTimbang->getJmlMatByKodeProductAndBatch($product['kode_product'], $no_batch);

        if ($jmlFormula == $jmlTimbang) {
            $getTotal = $this->MTimbang->getTotalPerBatch($kode_product, $no_batch);

            $actual_timbang = $getTotal['actual_timbang'];

            $sizePerSak = 40;
            $actualDivSak = floor($actual_timbang / $sizePerSak);
            $sisaTimbang = $actual_timbang - $actualDivSak;

            $token = $accurate['api_token'];
            $signature_secret = $accurate['signature_secret'];
            $timestamp = date("d/m/Y H:i:s");


            $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item-adjustment/save.do',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "adjustmentAccountNo":"",
                    "detailItem": [
                        {
                            "itemAdjustmentType":"ADJUSTMENT_IN",
                            "itemNo":"' . $itemNo . '",
                            "quantity":"' . $actualDivSak . '",
                            "detailNotes":"' . $no_batch . '"
                        }
                    ],
                    "transDate": "' . date('d/m/Y') . '",
                    "description":"' . $no_batch . '"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $token,
                    'X-Api-Timestamp: ' . $timestamp,
                    'X-Api-Signature: ' . $hash,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            // echo $response;

            $result = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data found',
                'detail' => json_decode($response, true)
            ];

            $this->output->set_output(json_encode($result));
        }
    }
}
