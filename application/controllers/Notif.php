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
            $jmlTimbang = $this->MTimbang->getJmlMatByKodeProduct($product['kode_product']);

            if ($jmlFormula == $jmlTimbang) {
                // Kalkulasi
                $introMsg = "Penimbangan batch *" . $no_batch . "* mengalami masalah berikut:\n";
                $errorMsg = "";

                $getHasilTimbang = $this->MTimbang->getByKodeProduct($product['kode_product'], $no_batch);

                foreach ($getHasilTimbang as $hasilTimbang) {
                    $kode_material = $hasilTimbang['kode_bahan'];
                    $name_material = $hasilTimbang['name_material'];
                    $actual_timbang = $hasilTimbang['actual_timbang'];

                    $getFormula = $this->MFormula->getByProductIdAndMaterial($product['id_product'], $kode_material);

                    $target_formula = $getFormula['target_formula'];
                    $fine_formula = $getFormula['fine_formula'];

                    $max_formula = $target_formula + $fine_formula;
                    $min_formula = $target_formula - $fine_formula;

                    if ($actual_timbang >= $max_formula) {
                        $difference = $actual_timbang - $target_formula;
                        $errorMsg = "- Material " . $name_material . " melebihi batas fine. Total penimbangan: " . $actual_timbang . "KG. Melebihi target timbang sebesar " . $difference . "KG. Target: " . $target_formula . "KG.";
                    } else if ($actual_timbang <= $min_formula) {
                        $difference = $target_formula - $actual_timbang;
                        $errorMsg = "- Material " . $name_material . " kurang dari batas fine. Total penimbangan: " . $actual_timbang . "KG. Lebih kecil target timbang sebesar " . $difference . "KG. Target: " . $target_formula . "KG.";
                    }
                }

                if ($errorMsg == "") {
                    $result = [
                        'code' => 401,
                        'status' => 'failed',
                        'msg' => 'Batch OK'
                    ];

                    $this->output->set_output(json_encode($result));
                } else {
                    // Send WA
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
