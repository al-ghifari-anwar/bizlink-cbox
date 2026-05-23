<?php

class Product extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
        $this->load->model('MProduct');
    }

    public function api()
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if (isset($_GET['source'])) {
                //     $source = $_GET['source'];

                //     $accurate = $this->MAccurate->get();

                //     $token = $accurate['api_token'];
                //     $signature_secret = $accurate['signature_secret'];
                //     $timestamp = date("d/m/Y H:i:s");

                //     $hash = base64_encode(hash_hmac('sha256', $timestamp, $signature_secret, true));

                //     $curl = curl_init();

                //     curl_setopt_array($curl, array(
                //         CURLOPT_URL => 'https://zeus.accurate.id/accurate/api/item/list.do?fields=id,name,no&filter.itemCategoryId.op=EQUAL&filter.itemCategoryId.val[0]=150&filter.itemCategoryId.val[1]=250&sp.pageSize=100',
                //         CURLOPT_RETURNTRANSFER => true,
                //         CURLOPT_ENCODING => '',
                //         CURLOPT_MAXREDIRS => 10,
                //         CURLOPT_TIMEOUT => 0,
                //         CURLOPT_FOLLOWLOCATION => true,
                //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                //         CURLOPT_CUSTOMREQUEST => 'GET',
                //         CURLOPT_HTTPHEADER => array(
                //             'Authorization: Bearer ' . $token,
                //             'X-Api-Timestamp: ' . $timestamp,
                //             'X-Api-Signature: ' . $hash,
                //             'Content-Type: application/json'
                //         ),
                //     ));

                //     $response = curl_exec($curl);

                //     $responseArray = json_decode($response, true);

                //     curl_close($curl);

                // $items = $responseArray['d'];

                $items = $this->getProducts();

                $result = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data found',
                    'detail' => $items,
                    // 'raw' => $responseArray
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

    public function api_by_id($id)
    {
        $this->output->set_content_type('application/json');

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $result = $this->MProduct->getById($id);

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            $this->output->set_output(json_encode($response));
        } else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $result = $this->MProduct->update($id);

            if ($result) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data updated'
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Data not updated',
                    'detail' => $this->db->error()
                ];

                $this->output->set_output(json_encode($response));
            }
        } else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
            $result = $this->MProduct->destroy($id);

            if ($result) {
                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data deleted'
                ];

                $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'Data not deleted',
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

    public function getProducts()
    {
        $products = [
            [
                'id' => 1,
                'no' => 'PRD001',
                'name' => 'Plester TKP',
            ],
            [
                'id' => 1,
                'no' => 'XPANDER-GROUT',
                'name' => 'XPANDER GROUT',
            ],
            [
                'id' => 1,
                'no' => 'PLAMIR',
                'name' => 'PLAMIR',
            ],
            [
                'id' => 1,
                'no' => '100012',
                'name' => 'ACIAN ABU',
            ],
            [
                'id' => 1,
                'no' => '100015',
                'name' => 'TA SUPER 40KG',
            ],
            [
                'id' => 1,
                'no' => '100016',
                'name' => 'TA PREMIUM 40KG',
            ],
            [
                'id' => 1,
                'no' => '100017',
                'name' => 'ACIAN PUTIH 40KG',
            ],
            [
                'id' => 1,
                'no' => '100014',
                'name' => 'TA ULTRA 40KG',
            ],
            [
                'id' => 1,
                'no' => 'PREMIX-THINBED',
                'name' => 'Premix Thinbed',
            ],
            [
                'id' => 1,
                'no' => '100023',
                'name' => 'THINBED TKP',
            ],
            [
                'id' => 1,
                'no' => '100006',
                'name' => 'THINBED 40KG',
            ],
            [
                'id' => 1,
                'no' => '100028',
                'name' => 'TA SUPER TKP',
            ],
            [
                'id' => 1,
                'no' => 'PREMIX-XPANDER-GROUT',
                'name' => 'PREMIX XPANDER GROUT',
            ],
            [
                'id' => 1,
                'no' => '100085',
                'name' => 'CLEANING KAPUR',
            ],
            [
                'id' => 1,
                'no' => '100087',
                'name' => 'PREMIX FLOOR SCREED',
            ],
            [
                'id' => 1,
                'no' => '100086',
                'name' => 'FLOOR SCREED',
            ],
            [
                'id' => 1,
                'no' => '100089',
                'name' => 'GM 380',
            ],
            [
                'id' => 1,
                'no' => '100090',
                'name' => 'TRIAL INVERTER',
            ],
            [
                'id' => 1,
                'no' => '100091',
                'name' => 'ADDITON ACTIVE 40KG',
            ],
            [
                'id' => 1,
                'no' => '100024',
                'name' => 'PLASTER 40 KG',
            ],
            [
                'id' => 1,
                'no' => '100092',
                'name' => 'FLOOR HARDENER GREEN',
            ],
            [
                'id' => 1,
                'no' => '100094',
                'name' => 'PW-940 PEREKAT KERAMIK',
            ],
            [
                'id' => 1,
                'no' => '100096',
                'name' => 'TRIAL_MORTARBIZLINK',
            ],
            [
                'id' => 1,
                'no' => '100100',
                'name' => 'PW-941 PEREKAT KERAMIK',
            ],
        ];

        return $products;
    }
}
