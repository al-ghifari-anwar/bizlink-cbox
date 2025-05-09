<?php

class Notif extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MFormula');
        $this->load->model('MProduct');
        $this->load->model('MQontak');
        $this->load->model('MTimbang');
        $this->load->model('MLogMsg');
        $this->load->model('MUser');
        $this->load->model('MaxchatHelper');
    }

    public function get()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

            $qontak = $this->MQontak->get();

            $no_batch = $post['no_batch'];
            $kode_product = $post['kode_product'];

            $product = $this->MProduct->getByKode($kode_product);
            $jmlFormula = $this->MFormula->getJmlByProductId($product['id_product']);
            $jmlTimbang = $this->MTimbang->getJmlMatByKodeProductAndBatch($product['kode_product'], $no_batch);

            if ($jmlFormula == $jmlTimbang) {
                // Kalkulasi
                $introMsg = "Penimbangan batch *" . $no_batch . "* mengalami masalah berikut:";
                $errorMsg = "";

                $getHasilTimbang = $this->MTimbang->getByKodeProductAndBatch($product['kode_product'], $no_batch);

                foreach ($getHasilTimbang as $hasilTimbang) {
                    $kode_material = $hasilTimbang['kode_bahan'];
                    $name_material = $hasilTimbang['name_bahan'];
                    $actual_timbang = $hasilTimbang['actual_timbang'];

                    $getFormula = $this->MFormula->getByProductIdAndMaterial($product['id_product'], $kode_material);

                    $target_formula = $getFormula['target_formula'];
                    $fine_formula = $getFormula['fine_formula'];

                    $max_formula = $target_formula + $fine_formula;
                    $min_formula = $target_formula - $fine_formula;

                    if ($actual_timbang > $max_formula) {
                        $difference = $actual_timbang - $target_formula;
                        // $errorMsg .= "- Material " . $name_material . " melebihi batas fine. Total penimbangan: " . $actual_timbang . "KG. Melebihi target timbang sebesar " . $difference . "KG. Target: " . $target_formula . "KG.\\\\n";

                        $errorMsg .= "- Material " . $name_material . " melebihi batas fine." . " Aktual: " . $actual_timbang . "" . " Selisih: " . $difference . "" . " Target: " . $target_formula;
                    } else if ($actual_timbang < $min_formula) {
                        $difference = $target_formula - $actual_timbang;
                        // $errorMsg .= "- Material " . $name_material . " kurang dari batas fine. Total penimbangan: " . $actual_timbang . "KG. Lebih kecil target timbang sebesar " . $difference . "KG. Target: " . $target_formula . "KG.\\\\n";
                        $errorMsg .= "- Material " . $name_material . " kurang dari batas fine." . " Aktual: " . $actual_timbang . "" . " Selisih: " . $difference . "" . " Target: " . $target_formula;
                    }
                }

                // Finish Good
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://bizlink.topmortarindonesia.com/api/finishgood',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => 'no_batch=' . $no_batch . '&kode_product=' . $kode_product,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/x-www-form-urlencoded'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);


                if ($errorMsg == "") {
                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Batch OK'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    $users = $this->MUser->get();
                    foreach ($users as $user) {

                        // Send WA
                        $nomor_hp = $user['phone_user'];
                        $nama = $user['name_user'];
                        $template_id = "9241bf86-ae94-4aa8-8975-551409af90b9";
                        $integration_id = $qontak['integration_id'];
                        $message = $introMsg . $errorMsg;
                        $full_name = "Miraswift";
                        $wa_token = $qontak['token'];

                        $messageRequest = [
                            'to' => $nomor_hp,
                            'msgType' => 'image',
                            'templateId' => 'b75d51f9-c925-4a62-8b93-dd072600b95b',
                            'values' => [
                                'body' => [
                                    [
                                        'index' => 1,
                                        'type' => 'text',
                                        'text' => $nama
                                    ],
                                    [
                                        'index' => 2,
                                        'type' => 'text',
                                        'text' => $message
                                    ]
                                ]
                            ]
                        ];

                        $send = $this->MaxchatHelper->sendMsg('https://app.maxchat.id/api/messages/push', $messageRequest);

                        // LOG 1
                        $dataLog1 = [
                            'to_name' => $nama,
                            'to_number' => $nomor_hp,
                            'message' => $message,
                            'date_msg' => date("Y-m-d H:i:s"),
                            'status_msg' => isset($send['content']) ? 'success' : 'failed',
                            'response_msg' => '',
                            'updated_at' => date("Y-m-d H:i:s")
                        ];
                        $log1 = $this->MLogMsg->createFromArray($dataLog1);
                    }

                    $result = [
                        'code' => 200,
                        'status' => 'ok',
                        'msg' => 'Notif OK',
                        'maxchat' => $send
                    ];

                    $this->output->set_output(json_encode($result));
                }
            } else {
                $result = [
                    'code' => 401,
                    'status' => 'failed',
                    'msg' => 'Data material timbang blm sesuai formula'
                ];

                $this->output->set_output(json_encode($result));
            }
        }
    }
}
