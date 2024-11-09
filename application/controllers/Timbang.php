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
        if ($qty <= $max_formula || $qty >= $min_formula) {
            // Nothing
        } else {
            if ($qty >= $max_formula) {
                $difference = $qty - $target_formula;
                $notifMessage = "Penimbangan untuk material " . $itemNo . " melebihi batas fine. Total penimbangan adalah " . $qty . "KG. Melebihi target timbang sebesar " . $difference . "KG. Target seharusnya adalah " . $target_formula . "KG.";
            } else if ($qty <= $min_formula) {
                $difference = $target_formula - $qty;
                $notifMessage = "Penimbangan untuk material " . $itemNo . " kurang dari batas fine. Total penimbangan adalah " . $qty . "KG. Lebih kecil target timbang sebesar " . $difference . "KG. Target seharusnya adalah " . $target_formula . "KG.";
            }

            // $nomor_hp = "6281808152028";
            $nomor_hp = "6285546112267";
            $nama = "Pak Hartawan";
            $template_id = "85f17083-255d-4340-af32-5dd22f483960";
            $integration_id = $qontak['integration_id'];
            $message = $notifMessage;
            $full_name = "Miraswift";
            $wa_token = $qontak['token'];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                        "to_number": "' . $nomor_hp . '",
                        "to_name": "' . $nama . '",
                        "message_template_id": "' . $template_id . '",
                        "channel_integration_id": "' . $integration_id . '",
                        "language": {
                            "code": "id"
                        },
                        "parameters": {
                            "body": [
                            {
                                "key": "1",
                                "value": "nama",
                                "value_text": "' . $nama . '"
                            },
                            {
                                "key": "2",
                                "value": "message",
                                "value_text": "' . $message . '"
                            },
                            {
                                "key": "3",
                                "value": "sales",
                                "value_text": "' . $full_name . '"
                            }
                            ]
                        }
                        }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $wa_token,
                    'Content-Type: application/json'
                ),
            ));

            $responseQontak = curl_exec($curl);

            curl_close($curl);

            $res = json_decode($responseQontak, true);
        }



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
            'qontak' => $res
        ];

        $this->output->set_output(json_encode($result));
    }
}
