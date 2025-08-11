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
    }

    public function saveTimbang()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $kode_bahan = $post['kode_bahan'];
        $no_batch = $post['no_batch'];
        $nama_bahan = $post['nama_bahan'];
        $actual_timbang = $post['actual_timbang'];
        $status_timbang = $post['status_timbang'];
        $date_timbang = $post['date_timbang'];
        $time_timbang = $post['time_timbang'];
        $kode_product = $post['kode_product'];

        $getInsertedTimbang = $this->MTimbang->getInsertedTimbang($no_batch, $nama_bahan, $kode_product);

        if ($getInsertedTimbang) {
            $id_timbang = $getInsertedTimbang['id_timbang'];

            $timbangData = [
                'kode_bahan' => $kode_bahan,
                'no_batch' => $no_batch,
                'name_bahan' => $nama_bahan,
                'actual_timbang' => $actual_timbang,
                'status_timbang' => $status_timbang,
                'date_timbang' => $date_timbang,
                'time_timbang' => $time_timbang,
                'created_at' => date('Y-m-d H:i:s'),
                'kode_product' => $kode_product,
            ];

            $save = $this->MTimbang->update($id_timbang, $timbangData);

            if ($save) {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data found',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Data not found',
                ];

                return $this->output->set_output(json_encode($result));
            }
        } else {
            $timbangData = [
                'kode_bahan' => $kode_bahan,
                'no_batch' => $no_batch,
                'name_bahan' => $nama_bahan,
                'actual_timbang' => $actual_timbang,
                'status_timbang' => $status_timbang,
                'date_timbang' => $date_timbang,
                'time_timbang' => $time_timbang,
                'created_at' => date('Y-m-d H:i:s'),
                'kode_product' => $kode_product,
            ];

            $save = $this->MTimbang->create($timbangData);

            if ($save) {
                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data found',
                ];

                return $this->output->set_output(json_encode($result));
            } else {
                $result = [
                    'code' => 400,
                    'status' => 'failed',
                    'msg' => 'Data not found',
                ];

                return $this->output->set_output(json_encode($result));
            }
        }
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $accurate = $this->MAccurate->get();

        $itemNo = $post['itemNo'];
        $no_batch = $post['no_batch'];
        $qty = $post['qty'];
        $kode_product = $post['kode_product'];

        $product = $this->MProduct->getByKode($kode_product);
        $formula = $this->MFormula->getByProductIdAndMaterial($product['id_product'], $itemNo);
        $qontak = $this->MQontak->get();

        $target_formula = $formula['target_formula'];
        $fine_formula = $formula['fine_formula'];
        $max_formula = $target_formula + $fine_formula;
        $min_formula = $target_formula - $fine_formula;

        $notifMessage = "";
        $res = array();

        if ($qty >= $max_formula) {
            $difference = $qty - $target_formula;
            $notifMessage = "Penimbangan untuk material " . $itemNo . " melebihi batas fine. Total penimbangan adalah " . $qty . "KG. Melebihi target timbang sebesar " . $difference . "KG. Target seharusnya adalah " . $target_formula . "KG.";
        } else if ($qty <= $min_formula) {
            $difference = $target_formula - $qty;
            $notifMessage = "Penimbangan untuk material " . $itemNo . " kurang dari batas fine. Total penimbangan adalah " . $qty . "KG. Lebih kecil target timbang sebesar " . $difference . "KG. Target seharusnya adalah " . $target_formula . "KG.";
        }

        // Webhook Notif
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://bizlink.topmortarindonesia.com/api//webhook/batch-notif',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => 'no_batch=' . $no_batch . '&kode_product=' . $kode_product,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Cookie: ci_session=1m9l9g3qge9u96qf3faps3uj9mhvoepd'
            ),
        ));

        $responseWebhook = curl_exec($curl);

        curl_close($curl);

        $resWebhook = json_decode($responseWebhook, true);

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
                        "itemAdjustmentType":"ADJUSTMENT_OUT",
                        "itemNo":"' . $itemNo . '",
                        "quantity":"' . $qty . '",
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
            'detail' => json_decode($response, true),
            'webhook' => $resWebhook
        ];

        $this->output->set_output(json_encode($result));
    }
}
