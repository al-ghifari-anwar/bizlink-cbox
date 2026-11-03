<?php

class Item extends CI_Controller
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
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $accurate = $this->MAccurate->get();

            $name = $post['name'];
            $no_batch = $post['no_batch'];

            $token = $accurate['api_token'];
            $signature_secret = $accurate['signature_secret'];
            $timestamp = date("d/m/Y H:i:s");
            // $signature_secret = "31d49b3dc632614495ff8071e5be44a1";
            // $timestamp = "02/11/2023 09:01:01";

            $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item/save.do',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "itemType":"INVENTORY",
                    "name":"' . $name . '",
                    "no":"' . $no_batch . '",
                    "serialNumberType":"BATCH",
                    "weight":"2000"
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
        } else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['no'])) {
                $no = $_GET['no'];
                $accurate = $this->MAccurate->get();

                $token = $accurate['api_token'];
                $signature_secret = $accurate['signature_secret'];
                $timestamp = date("d/m/Y H:i:s");

                $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item/detail.do?no=' . $no,
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

                curl_close($curl);

                echo $response;
            } else {
                // $accurate = $this->MAccurate->get();

                // $token = $accurate['api_token'];
                // $signature_secret = $accurate['signature_secret'];
                // $timestamp = date("d/m/Y H:i:s");

                // $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

                // $curl = curl_init();

                // curl_setopt_array($curl, array(
                //     CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item/list.do?fields=id,name,no&filter.itemCategoryId.op=EQUAL&filter.itemCategoryId.val[0]=100&filter.itemCategoryId.val[1]=250&sp.pageSize=100',
                //     CURLOPT_RETURNTRANSFER => true,
                //     CURLOPT_ENCODING => '',
                //     CURLOPT_MAXREDIRS => 10,
                //     CURLOPT_TIMEOUT => 0,
                //     CURLOPT_FOLLOWLOCATION => true,
                //     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //     CURLOPT_CUSTOMREQUEST => 'GET',
                //     CURLOPT_HTTPHEADER => array(
                //         'Authorization: Bearer ' . $token,
                //         'X-Api-Timestamp: ' . $timestamp,
                //         'X-Api-Signature: ' . $hash,
                //         'Content-Type: application/json'
                //     ),
                // ));

                // $response = curl_exec($curl);

                // $responseArray = json_decode($response, true);

                // curl_close($curl);

                // $items = $responseArray['d'];

                $items = $this->getItems();

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

    public function getItems()
    {
        $items = [
            ['id' => 1, 'no' => '100009', 'name' => 'Aluminium Powder'],
            ['id' => 1, 'no' => '1005', 'name' => 'HPMC 40000'],
            ['id' => 1, 'no' => '1005', 'name' => 'HPMC 40000 ADTF'],
            ['id' => 1, 'no' => '1002', 'name' => 'Kapur'],
            ['id' => 1, 'no' => '1003', 'name' => 'Pasir 30 up'],
            ['id' => 1, 'no' => '100007', 'name' => 'Pasir Kasar 16 -30'],
            ['id' => 1, 'no' => 'PREMIX-ACIAN PUTIH', 'name' => 'Premix Acian Putih'],
            ['id' => 1, 'no' => '100093', 'name' => 'PREMIX FL.HARDENER GREEN'],
            ['id' => 1, 'no' => '100087', 'name' => 'PREMIX FLOOR SCREED'],
            ['id' => 1, 'no' => '100088', 'name' => 'PREMIX GM380'],
            ['id' => 1, 'no' => 'PREMIX-PLAMIR', 'name' => 'Premix Plamir'],
            ['id' => 1, 'no' => '100098', 'name' => 'Premix Plaster'],
            ['id' => 1, 'no' => '100095', 'name' => 'PREMIX PW-940'],
            ['id' => 1, 'no' => '100099', 'name' => 'PREMIX PW-941'],
            ['id' => 1, 'no' => 'PREMIX-TA PREMIUM TM', 'name' => 'Premix TA Premium TM'],
            ['id' => 1, 'no' => 'PREMIX-TA SUPER TM', 'name' => 'Premix TA Super TM'],
            ['id' => 1, 'no' => 'PREMIX-TA ULTRA TM', 'name' => 'Premix TA Ultra TM'],
            ['id' => 1, 'no' => 'PREMIX-THINBED', 'name' => 'Premix Thindbed'],
            ['id' => 1, 'no' => 'PREMIX-XPANDER-GROUT', 'name' => 'Premix Xpander Grout'],
            ['id' => 1, 'no' => '100013', 'name' => 'PREMIX-ACIAN'],
            ['id' => 1, 'no' => '1001', 'name' => 'Semen'],
            ['id' => 1, 'no' => '1001', 'name' => 'SEMEN GREY'],
            ['id' => 1, 'no' => '1004', 'name' => 'SEMEN PUTIH']
        ];

        return $items;
    }
}
