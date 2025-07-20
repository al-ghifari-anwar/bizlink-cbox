<?php

class Batch extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MAccurate');
        $this->load->model('MEquipmentStatus');
        $this->load->model('MTimbang');
        $this->load->model('MProduct');
        $this->load->model('MFormula');
        $this->load->model('MSpk');
    }

    public function index()
    {
        // $this->output->set_content_type('application/json');
        $daterange = $this->input->post('date_range');
        $dateFrom = date("Y-m-d");
        $dateTo = date("Y-m-d");

        if ($daterange) {
            $dates = explode('-', $daterange);
            $dateFrom = date("Y-m-d", strtotime($dates[0]));
            $dateTo = date("Y-m-d", strtotime($dates[1]));
        }

        $data['title'] = 'Rekap Produksi';

        $batchs = $this->MEquipmentStatus->getByDaterange($dateFrom, $dateTo);

        $batchArray = array();

        foreach ($batchs as $batch) {
            $equipmentArray = array();
            $timbangArray = array();

            $timbang = $this->MTimbang->getPrdByBatch($batch['no_batch']);
            $kode_product = $timbang != null ? $timbang['kode_product'] : '';
            $product = $this->MProduct->getByKode($kode_product);

            $no_batch = $batch['no_batch'];
            $timbang = $this->MTimbang->getPrdByBatch($batch['no_batch']);
            $kode_product = $timbang != null ? $timbang['kode_product'] : '';
            $product = $this->MProduct->getByKode($kode_product);

            // Timbang
            $timbangs = $this->MTimbang->getByBatch($no_batch);

            $totalMaterialTime = new DateTime("00:00:00");
            $cloneTotalMaterialTime = clone $totalMaterialTime;
            foreach ($timbangs as $timbang) {
                // Data Timbang
                $kode_material = $timbang['kode_bahan'];
                $formulaMaterial = $this->MFormula->getByProductIdAndMaterial($product['id_product'], $kode_material);

                if ($formulaMaterial != null) {

                    // Time Timbang
                    $name_equipment = "";
                    if ($timbang['kode_bahan'] == '1001') {
                        $name_equipment = 'PENIMBANGAN SEMEN';
                    } else if ($timbang['kode_bahan'] == '1004') {
                        $name_equipment = 'PENIMBANGAN SEMEN PUTIH';
                    } else if ($timbang['kode_bahan'] == '1002') {
                        $name_equipment = 'PENIMBANGAN KAPUR';
                    } else if ($timbang['kode_bahan'] == '1003') {
                        $name_equipment = 'PENIMBANGAN PASIR HALUS';
                    } else if ($timbang['kode_bahan'] == '100007') {
                        $name_equipment = 'PENIMBANGAN PASIR KASAR';
                    } else if (str_contains($formulaMaterial['name_material'], 'PREMIX') || str_contains($formulaMaterial['name_material'], 'premix') || str_contains($formulaMaterial['name_material'], 'Premix') || str_contains($formulaMaterial['name_material'], 'ADTF') || str_contains($formulaMaterial['name_material'], 'adtf')) {
                        $name_equipment = 'PENIMBANGAN ADDITIF';
                    }
                    $getEquipmentTimbangOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);
                    $getEquipmentTimbangOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);

                    if ($getEquipmentTimbangOn != null) {
                        $timeOn = $getEquipmentTimbangOn['date_equipment'] . " " . $getEquipmentTimbangOn['time_equipment'];
                        $timeOff = $getEquipmentTimbangOff['date_equipment'] . " " . $getEquipmentTimbangOff['time_equipment'];

                        $date1 = new DateTime($timeOn);
                        $date2 = new DateTime($timeOff);
                        $diference  = $date2->diff($date1);
                        $interval = $this->format_interval($diference);

                        $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                        $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                        $timeDiff = $time1->diff($time2);
                        $totalMaterialTime->add($timeDiff);
                        $intervalTotalMaterial = $cloneTotalMaterialTime->diff($totalMaterialTime)->format("%H:%i:%s");

                        // Set Response Timbang
                        $timbang['materialTime'] = $interval;
                        $timbang['formula'] = $formulaMaterial;
                        $getTimbangWithResep[] = $timbang;

                        $response = [
                            'no_batch' => $no_batch,
                            'name_equipment' => $name_equipment,
                            'name_bahan' => $timbang['name_bahan'],
                            'date' => $batch['date_equipment'],
                            'timeOn' => $timeOn,
                            'timeOff' => $timeOff,
                            'time' => $interval,
                            'actual' => $timbang['actual_timbang'],
                        ];

                        array_push($batchArray, $response);
                    }
                }
            }

            $exceptName = ['PENIMBANGAN SEMEN', 'PENIMBANGAN SEMEN PUTIH', 'PENIMBANGAN KAPUR', 'PENIMBANGAN PASIR HALUS', 'PENIMBANGAN PASIR KASAR', 'PENIMBANGAN ADDITIF'];

            $equipments = $this->MEquipmentStatus->getEquipmentNoBahan($no_batch, $exceptName);

            $totalEquipmentTime = new DateTime('00:00:00');
            $cloneTotalEquipmentTime = clone $totalEquipmentTime;
            foreach ($equipments as $equipment) {
                $name_equipment = $equipment['name_equipment'];

                $getEquipmentOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);

                if ($getEquipmentOn) {
                    $getEquipmentOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);
                    $timeOn = $getEquipmentOn['date_equipment'] . " " . $getEquipmentOn['time_equipment'];


                    if ($getEquipmentOff != null) {
                        $timeOff = $getEquipmentOff['date_equipment'] . " " . $getEquipmentOff['time_equipment'];

                        $date1 = new DateTime($timeOn);
                        $date2 = new DateTime($timeOff);
                        $diference  = $date2->diff($date1);
                        $interval = $this->format_interval($diference);

                        if ($equipment['name_equipment'] != 'MIXING TIME') {
                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalEquipmentTime->add($timeDiff);
                        }

                        $response = [
                            'no_batch' => $no_batch,
                            'name_equipment' => $name_equipment,
                            'date' => $batch['date_equipment'],
                            'name_bahan' => '-',
                            'timeOn' => $timeOn,
                            'timeOff' => $timeOff,
                            'time' => $interval,
                            'actual' => '-',
                        ];

                        array_push($batchArray, $response);
                    }
                }
            }
        }

        $data['batchs'] = $batchArray;


        // return $this->output->set_output(json_encode($batchArray));

        $this->load->view('Theme/Header', $data);
        $this->load->view('Theme/Menu');
        $this->load->view('Batch/Index');
        $this->load->view('Theme/Footer');
        $this->load->view('Theme/Scripts');
    }

    public function fastest($id_product)
    {
        $this->output->set_content_type('application/json');

        $batchs = $this->MEquipmentStatus->getByIdProduct($id_product);

        if (!$batchs) {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'No production data',
            ];

            return $this->output->set_output(json_encode($response));
        } else {

            $resultArray = array();

            foreach ($batchs as $batch) {
                $no_batch = $batch['no_batch'];

                // Calculate Time
                $resultBatch = $this->MEquipmentStatus->getByBatch($no_batch);

                $resultEquipment = $this->MEquipmentStatus->getEquipmentByBatch($no_batch);

                if ($resultEquipment == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Data Equipment Tidak Ditemukan'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getTimbang = $this->MTimbang->getByBatch($no_batch);

                if ($getTimbang == null && $resultEquipment != null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Batch ' . $no_batch . ' tidak memiliki data timbang, tetapi memiliki data equipment. Kemungkinan terjadi karna ketidakcocokan no batch'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getKodeProduct = $this->MTimbang->getPrdByBatch($no_batch);
                $kode_product = $getKodeProduct['kode_product'];

                $getProduct = $this->MProduct->getByKode($kode_product);

                if ($getProduct == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Produk dengan kode ' . $kode_product . ' tidak dapat ditemukan dalam database.'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $rekapEquipment = array();

                $getTimbangWithResep = array();
                // Inject Resep
                $totalMaterialTime = new DateTime("00:00:00");
                $cloneTotalMaterialTime = clone $totalMaterialTime;
                foreach ($getTimbang as $timbang) {
                    // Data Timbang
                    $kode_material = $timbang['kode_bahan'];
                    $formulaMaterial = $this->MFormula->getByProductIdAndMaterial($getProduct['id_product'], $kode_material);

                    if ($formulaMaterial != null) {

                        // Time Timbang
                        $name_equipment = "";
                        if ($timbang['kode_bahan'] == '1001') {
                            $name_equipment = 'PENIMBANGAN SEMEN';
                        } else if ($timbang['kode_bahan'] == '1004') {
                            $name_equipment = 'PENIMBANGAN SEMEN PUTIH';
                        } else if ($timbang['kode_bahan'] == '1002') {
                            $name_equipment = 'PENIMBANGAN KAPUR';
                        } else if ($timbang['kode_bahan'] == '1003') {
                            $name_equipment = 'PENIMBANGAN PASIR HALUS';
                        } else if ($timbang['kode_bahan'] == '100007') {
                            $name_equipment = 'PENIMBANGAN PASIR KASAR';
                        } else if (str_contains($formulaMaterial['name_material'], 'PREMIX') || str_contains($formulaMaterial['name_material'], 'premix') || str_contains($formulaMaterial['name_material'], 'Premix') || str_contains($formulaMaterial['name_material'], 'ADTF') || str_contains($formulaMaterial['name_material'], 'adtf')) {
                            $name_equipment = 'PENIMBANGAN ADDITIF';
                        }
                        $getEquipmentTimbangOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);
                        $getEquipmentTimbangOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);

                        if ($getEquipmentTimbangOn != null) {
                            $timeOn = $getEquipmentTimbangOn['date_equipment'] . " " . $getEquipmentTimbangOn['time_equipment'];
                            $timeOff = $getEquipmentTimbangOff['date_equipment'] . " " . $getEquipmentTimbangOff['time_equipment'];

                            $date1 = new DateTime($timeOn);
                            $date2 = new DateTime($timeOff);
                            $diference  = $date2->diff($date1);
                            $interval = $this->format_interval($diference);

                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalMaterialTime->add($timeDiff);
                            $intervalTotalMaterial = $cloneTotalMaterialTime->diff($totalMaterialTime)->format("%H:%i:%s");

                            // Set Response Timbang
                            $timbang['materialTime'] = $interval;
                            $timbang['formula'] = $formulaMaterial;
                            $getTimbangWithResep[] = $timbang;
                        }
                    }
                }

                $totalEquipmentTime = new DateTime('00:00:00');
                $cloneTotalEquipmentTime = clone $totalEquipmentTime;
                foreach ($resultEquipment as $equipment) {
                    $name_equipment = $equipment['name_equipment'];

                    $getEquipmentOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);

                    if ($getEquipmentOn == null) {
                        $response = [
                            'code' => 401,
                            'status' => 'ok',
                            'msg' => 'Equipment ' . $name_equipment . 'Batch: ' . $no_batch . ' tidak memiliki data ON atau status ketika euipment start'
                        ];

                        return $this->output->set_output(json_encode($response));
                    }
                    $getEquipmentOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);
                    $timeOn = $getEquipmentOn['date_equipment'] . " " . $getEquipmentOn['time_equipment'];


                    if ($getEquipmentOff != null) {
                        $timeOff = $getEquipmentOff['date_equipment'] . " " . $getEquipmentOff['time_equipment'];

                        $date1 = new DateTime($timeOn);
                        $date2 = new DateTime($timeOff);
                        $diference  = $date2->diff($date1);
                        $interval = $this->format_interval($diference);

                        if ($equipment['name_equipment'] != 'MIXING TIME') {
                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalEquipmentTime->add($timeDiff);
                        }

                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => $timeOff,
                            'time_elapsed' => $interval,
                            'desc' => 'finished'
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    } else {
                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => "0",
                            'time_elapsed' => "0",
                            'desc' => 'running'
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    }
                }


                $interval = $cloneTotalEquipmentTime->diff($totalEquipmentTime);

                $intervalTotalEquipment = sprintf(
                    "%02d:%02d:%02d",
                    $interval->h + ($interval->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                    $interval->i,
                    $interval->s
                );

                // Mixing Time Only
                $totalMixingTime = new DateTime('00:00:00');
                $cloneTotalMixingTime = clone $totalMixingTime;

                $getMixingTimeOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, 'MIXING TIME');
                if ($getMixingTimeOn) {
                    $mixingTimeimeOn = $getMixingTimeOn['date_equipment'] . " " . $getMixingTimeOn['time_equipment'];
                    $getMixingTimeOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, 'MIXING TIME');
                    if ($getMixingTimeOff) {
                        $mixingTimeimeOff = $getMixingTimeOff['date_equipment'] . " " . $getMixingTimeOff['time_equipment'];

                        $mixingTime1 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOn)));
                        $mixingTime2 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOff)));
                        $mixingTimeimeDiff = $mixingTime1->diff($mixingTime2);
                        $totalMixingTime->add($mixingTimeimeDiff);

                        $intervalMixingTime = $cloneTotalMixingTime->diff($totalMixingTime);

                        $intervalTotalMixingTime = sprintf(
                            "%02d:%02d:%02d",
                            $intervalMixingTime->h + ($intervalMixingTime->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                            $intervalMixingTime->i,
                            $intervalMixingTime->s
                        );

                        // Kurangi Mixing Time
                        $resEquipmentTime = strtotime("1970-01-01 " . $intervalTotalEquipment);
                        $resMixingTime = strtotime("1970-01-01 " . $intervalTotalMixingTime);

                        $resultTotalTime = $resEquipmentTime - $resMixingTime;

                        $resultTimeFormat = gmdate("H:i:s", abs($resultTotalTime));
                    } else {
                        $resultTimeFormat = $intervalTotalEquipment;
                    }
                } else {
                    $resultTimeFormat = $intervalTotalEquipment;
                }

                // DelayTime
                $totalFeedingTime = new DateTime('00:00:00');
                $cloneTotalFeedingTime = clone $totalFeedingTime;

                $getFirstPenimbanganOn = $this->MEquipmentStatus->getFirstPenimbanganOn($no_batch);
                $penimbanganOn = $this->MEquipmentStatus->getById($getFirstPenimbanganOn['id_equipment_status']);
                $timeOn = $penimbanganOn['date_equipment'] . " " . $penimbanganOn['time_equipment'];

                $getLastPenimbanganOff = $this->MEquipmentStatus->getLastPenimbanganOff($no_batch);
                $penimbanganOff = $this->MEquipmentStatus->getById($getLastPenimbanganOff['id_equipment_status']);
                $timeOff = $penimbanganOff['date_equipment'] . " " . $penimbanganOff['time_equipment'];

                $feedingTime1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                $feedingTime2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                $feedingTimeDiff = $feedingTime1->diff($feedingTime2);
                $totalFeedingTime->add($feedingTimeDiff);

                $intervalFeedingTime = $cloneTotalFeedingTime->diff($totalFeedingTime);

                $intervalTotalFeedingTime = sprintf(
                    "%02d:%02d:%02d",
                    $intervalFeedingTime->h + ($intervalFeedingTime->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                    $intervalFeedingTime->i,
                    $intervalFeedingTime->s
                );

                // Kurangi Mixing Time
                $resFeedingTime = strtotime("1970-01-01 " . $intervalTotalFeedingTime);
                $resMaterialTime = strtotime("1970-01-01 " . $intervalTotalMaterial);

                $delayTime = $resFeedingTime - $resMaterialTime;
                $resultDelayTime = gmdate("H:i:s", abs($delayTime));
                // End of delay time

                // Cycle Time
                // Konversi ke detik
                list($h1, $m1, $s1) = explode(":", $resultTimeFormat);
                list($h2, $m2, $s2) = explode(":", $resultDelayTime);

                $scndEquipmentTime = $h1 * 3600 + $m1 * 60 + $s1;
                $scndDelayTime = $h2 * 3600 + $m2 * 60 + $s2;

                // Jumlahkan
                $cycleTime = $scndEquipmentTime + $scndDelayTime;

                $resultCycleTime = gmdate("H:i:s", abs($cycleTime));

                // $timbang = $this->MTimbang->getPrdByBatch($no_batch);
                $kode_product = $kode_product;
                // echo json_encode($timbang);
                // die;
                // Push to new array
                $spk = $this->MSpk->getById($batch['id_spk']);

                $resultCalculate = [
                    'no_batch' => $no_batch,
                    'date_equipment' => $batch['date_equipment'],
                    'totalEquipmentTime' => $resultCycleTime,
                    'product' => $getProduct,
                    'spk' => $spk != null ? $spk : array(),
                ];

                array_push($resultArray, $resultCalculate);
            }

            // $response = [
            //     'code' => 200,
            //     'status' => 'ok',
            //     'msg' => 'Data fetched',
            //     'totalEquipmentTime' => $intervalTotalEquipment,
            //     'totalMaterialTime' => $intervalTotalMaterial,
            //     'product' => $kode_product,
            //     'dataEquipment' => $rekapEquipment,
            //     'dataTimbang' => $getTimbangWithResep,
            //     'dataProduct' => $getProduct,
            // ];

            // Urutkan berdasarkan totalEquipmentTime dari yang tercepat
            usort($resultArray, function ($a, $b) {
                $timeA = strtotime("1970-01-01 " . $a['totalEquipmentTime']);
                $timeB = strtotime("1970-01-01 " . $b['totalEquipmentTime']);
                return $timeA - $timeB;
            });

            // Ambil 2 data pertama setelah diurutkan
            $fastestBatch = array_slice($resultArray, 0, 2);


            $response = [
                'code' => 200,
                'status' => 'ok',
                'data' => $fastestBatch,
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    public function get()
    {
        $this->output->set_content_type('application/json');

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $batch = isset($_GET['batch']) ? $_GET['batch'] : null;
            $date = isset($_GET['date']) ? $_GET['date'] : null;
            $prd = isset($_GET['prd']) ? $_GET['prd'] : null;

            $completeBatch = array();

            $batchs = $this->MEquipmentStatus->getByFilter($batch, $date, $prd);

            foreach ($batchs as $batch) {
                $timbang = $this->MTimbang->getPrdByBatch($batch['no_batch']);
                $kode_product = $timbang != null ? $timbang['kode_product'] : '';
                $product = $this->MProduct->getByKode($kode_product);
                // echo json_encode($timbang);
                // die;
                $spk = $this->MSpk->getById($batch['id_spk']);
                $batch['product'] = $product;
                $batch['spk'] = $spk != null ? $spk : array();
                // Push to new array
                $completeBatch[] = $batch;
            }

            $result = $completeBatch;

            $response = [
                'code' => 200,
                'status' => 'ok',
                'msg' => 'Data fetched',
                'data' => $result
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    public function detail($no_batch)
    {
        $this->output->set_content_type('application/json');

        // $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            if ($no_batch != null) {
                $resultBatch = $this->MEquipmentStatus->getByBatch($no_batch);

                $resultEquipment = $this->MEquipmentStatus->getEquipmentByBatch($no_batch);

                if ($resultEquipment == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Data Equipment Tidak Ditemukan'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getTimbang = $this->MTimbang->getByBatch($no_batch);

                if ($getTimbang == null && $resultEquipment != null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Batch ' . $no_batch . ' tidak memiliki data timbang, tetapi memiliki data equipment. Kemungkinan terjadi karna ketidakcocokan no batch'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $getKodeProduct = $this->MTimbang->getPrdByBatch($no_batch);
                $kode_product = $getKodeProduct['kode_product'];

                $getProduct = $this->MProduct->getByKode($kode_product);

                if ($getProduct == null) {
                    $response = [
                        'code' => 401,
                        'status' => 'ok',
                        'msg' => 'Produk dengan kode ' . $kode_product . ' tidak dapat ditemukan dalam database.'
                    ];

                    return $this->output->set_output(json_encode($response));
                }

                $rekapEquipment = array();

                $getTimbangWithResep = array();
                // Inject Resep
                $totalMaterialTime = new DateTime("00:00:00");
                $cloneTotalMaterialTime = clone $totalMaterialTime;
                foreach ($getTimbang as $timbang) {
                    // Data Timbang
                    $kode_material = $timbang['kode_bahan'];
                    $formulaMaterial = $this->MFormula->getByProductIdAndMaterial($getProduct['id_product'], $kode_material);

                    if ($formulaMaterial != null) {

                        // Time Timbang
                        $name_equipment = "";
                        if ($timbang['kode_bahan'] == '1001') {
                            $name_equipment = 'PENIMBANGAN SEMEN';
                        } else if ($timbang['kode_bahan'] == '1004') {
                            $name_equipment = 'PENIMBANGAN SEMEN PUTIH';
                        } else if ($timbang['kode_bahan'] == '1002') {
                            $name_equipment = 'PENIMBANGAN KAPUR';
                        } else if ($timbang['kode_bahan'] == '1003') {
                            $name_equipment = 'PENIMBANGAN PASIR HALUS';
                        } else if ($timbang['kode_bahan'] == '100007') {
                            $name_equipment = 'PENIMBANGAN PASIR KASAR';
                        } else if (str_contains($formulaMaterial['name_material'], 'PREMIX') || str_contains($formulaMaterial['name_material'], 'premix') || str_contains($formulaMaterial['name_material'], 'Premix') || str_contains($formulaMaterial['name_material'], 'ADTF') || str_contains($formulaMaterial['name_material'], 'adtf')) {
                            $name_equipment = 'PENIMBANGAN ADDITIF';
                        }
                        $getEquipmentTimbangOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);
                        $getEquipmentTimbangOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);

                        if ($getEquipmentTimbangOn != null) {
                            $timeOn = $getEquipmentTimbangOn['date_equipment'] . " " . $getEquipmentTimbangOn['time_equipment'];
                            $timeOff = $getEquipmentTimbangOff['date_equipment'] . " " . $getEquipmentTimbangOff['time_equipment'];

                            $date1 = new DateTime($timeOn);
                            $date2 = new DateTime($timeOff);
                            $diference  = $date2->diff($date1);
                            $interval = $this->format_interval($diference);

                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalMaterialTime->add($timeDiff);
                            // $intervalTotalMaterial = $cloneTotalMaterialTime->diff($totalMaterialTime)->format("%H:%i:%s");

                            $intervalMaterial = $cloneTotalMaterialTime->diff($totalMaterialTime);

                            $intervalTotalMaterial = sprintf(
                                "%02d:%02d:%02d",
                                $intervalMaterial->h + ($intervalMaterial->d * 24), // jika inter$intervalMaterial lebih dari 1 hari, jam harus ditambah
                                $intervalMaterial->i,
                                $intervalMaterial->s
                            );

                            // Set Response Timbang
                            $timbang['materialTime'] = $interval;
                            $timbang['formula'] = $formulaMaterial;
                            $getTimbangWithResep[] = $timbang;
                        }
                    }
                }

                $totalEquipmentTime = new DateTime('00:00:00');
                $cloneTotalEquipmentTime = clone $totalEquipmentTime;
                foreach ($resultEquipment as $equipment) {
                    $name_equipment = $equipment['name_equipment'];

                    $getEquipmentOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, $name_equipment);

                    if ($getEquipmentOn == null) {
                        $response = [
                            'code' => 401,
                            'status' => 'ok',
                            'msg' => 'Equipment ' . $name_equipment . ' tidak memiliki data ON atau status ketika euipment start'
                        ];

                        return $this->output->set_output(json_encode($response));
                    }
                    $getEquipmentOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, $name_equipment);
                    $timeOn = $getEquipmentOn['date_equipment'] . " " . $getEquipmentOn['time_equipment'];


                    if ($getEquipmentOff != null) {
                        $timeOff = $getEquipmentOff['date_equipment'] . " " . $getEquipmentOff['time_equipment'];

                        $date1 = new DateTime($timeOn);
                        $date2 = new DateTime($timeOff);
                        $diference  = $date2->diff($date1);
                        $interval = $this->format_interval($diference);

                        if ($equipment['name_equipment'] != 'MIXING TIME') {
                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalEquipmentTime->add($timeDiff);
                        }

                        if ($equipment['name_equipment'] != 'MIXER') {
                            $dataEquipment = [
                                'no_batch' => $equipment['no_batch'],
                                'name_equipment' => $equipment['name_equipment'],
                                'time_on' => $timeOn,
                                'time_off' => $timeOff,
                                'time_elapsed' => $interval,
                                'desc' => 'finished'
                            ];

                            $rekapEquipment[] = $dataEquipment;
                        } else {
                            $totalMixerTime = new DateTime('00:00:00');
                            $cloneTotalMixerTime = clone $totalMixerTime;

                            $time1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                            $time2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                            $timeDiff = $time1->diff($time2);
                            $totalMixerTime->add($timeDiff);

                            $intervalMixer = $cloneTotalMixerTime->diff($totalMixerTime);

                            $intervalTotalMixer = sprintf(
                                "%02d:%02d:%02d",
                                $intervalMixer->h + ($intervalMixer->d * 24), // jika inter$intervalMixer lebih dari 1 hari, jam harus ditambah
                                $intervalMixer->i,
                                $intervalMixer->s
                            );
                            // Kurangi Mixer dengan Mixing Time
                            // Mixing Time Only
                            $totalMixingTime = new DateTime('00:00:00');
                            $cloneTotalMixingTime = clone $totalMixingTime;

                            $getMixingTimeOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, 'MIXING TIME');
                            if ($getMixingTimeOn) {
                                $mixingTimeimeOn = $getMixingTimeOn['date_equipment'] . " " . $getMixingTimeOn['time_equipment'];
                                $getMixingTimeOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, 'MIXING TIME');
                                if ($getMixingTimeOff) {
                                    $mixingTimeimeOff = $getMixingTimeOff['date_equipment'] . " " . $getMixingTimeOff['time_equipment'];

                                    $mixingTime1 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOn)));
                                    $mixingTime2 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOff)));
                                    $mixingTimeimeDiff = $mixingTime1->diff($mixingTime2);
                                    $totalMixingTime->add($mixingTimeimeDiff);

                                    $intervalMixingTime = $cloneTotalMixingTime->diff($totalMixingTime);

                                    $intervalTotalMixingTime = sprintf(
                                        "%02d:%02d:%02d",
                                        $intervalMixingTime->h + ($intervalMixingTime->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                                        $intervalMixingTime->i,
                                        $intervalMixingTime->s
                                    );

                                    // Kurangi Mixing Time
                                    $resMixerTime = strtotime("1970-01-01 " . $intervalTotalMixer);
                                    $resMixingTime = strtotime("1970-01-01 " . $intervalTotalMixingTime);

                                    $resultTotalDischarge = $resMixerTime - $resMixingTime;

                                    $resultDischareTime = gmdate("H:i:s", abs($resultTotalDischarge));
                                } else {
                                    $resultDischareTime = $intervalTotalMixer;
                                }
                            } else {
                                $resultDischareTime = $intervalTotalMixer;
                            }

                            $dataEquipment = [
                                'no_batch' => $equipment['no_batch'],
                                'name_equipment' => $equipment['name_equipment'],
                                'time_on' => $timeOn,
                                'time_off' => $timeOff,
                                'time_elapsed' => $this->formatWaktuToSingkat($resultDischareTime),
                                'desc' => 'finished'
                            ];

                            $rekapEquipment[] = $dataEquipment;
                        }
                    } else {
                        $dataEquipment = [
                            'no_batch' => $equipment['no_batch'],
                            'name_equipment' => $equipment['name_equipment'],
                            'time_on' => $timeOn,
                            'time_off' => "0",
                            'time_elapsed' => "0",
                            'desc' => 'running'
                        ];

                        $rekapEquipment[] = $dataEquipment;
                    }
                }


                // $intervalTotalEquipment = $cloneTotalEquipmentTime->diff($totalEquipmentTime)->format("%H:%i:%s");

                $intervalEquipment = $cloneTotalEquipmentTime->diff($totalEquipmentTime);

                $intervalTotalEquipment = sprintf(
                    "%02d:%02d:%02d",
                    $intervalEquipment->h + ($intervalEquipment->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                    $intervalEquipment->i,
                    $intervalEquipment->s
                );

                // Mixing Time Only
                $totalMixingTime = new DateTime('00:00:00');
                $cloneTotalMixingTime = clone $totalMixingTime;

                $getMixingTimeOn = $this->MEquipmentStatus->getEquipmentOn($no_batch, 'MIXING TIME');
                if ($getMixingTimeOn) {
                    $mixingTimeimeOn = $getMixingTimeOn['date_equipment'] . " " . $getMixingTimeOn['time_equipment'];
                    $getMixingTimeOff = $this->MEquipmentStatus->getEquipmentOff($no_batch, 'MIXING TIME');
                    if ($getMixingTimeOff) {
                        $mixingTimeimeOff = $getMixingTimeOff['date_equipment'] . " " . $getMixingTimeOff['time_equipment'];

                        $mixingTime1 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOn)));
                        $mixingTime2 = new DateTime(date("H:i:s", strtotime($mixingTimeimeOff)));
                        $mixingTimeimeDiff = $mixingTime1->diff($mixingTime2);
                        $totalMixingTime->add($mixingTimeimeDiff);

                        $intervalMixingTime = $cloneTotalMixingTime->diff($totalMixingTime);

                        $intervalTotalMixingTime = sprintf(
                            "%02d:%02d:%02d",
                            $intervalMixingTime->h + ($intervalMixingTime->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                            $intervalMixingTime->i,
                            $intervalMixingTime->s
                        );

                        // Kurangi Mixing Time
                        $resEquipmentTime = strtotime("1970-01-01 " . $intervalTotalEquipment);
                        $resMixingTime = strtotime("1970-01-01 " . $intervalTotalMixingTime);

                        $resultTotalTime = $resEquipmentTime - $resMixingTime;

                        $resultTimeFormat = gmdate("H:i:s", abs($resultTotalTime));
                    } else {
                        $resultTimeFormat = $intervalTotalEquipment;
                    }
                } else {
                    $resultTimeFormat = $intervalTotalEquipment;
                }

                // DelayTime
                $totalFeedingTime = new DateTime('00:00:00');
                $cloneTotalFeedingTime = clone $totalFeedingTime;

                $getFirstPenimbanganOn = $this->MEquipmentStatus->getFirstPenimbanganOn($no_batch);
                $penimbanganOn = $this->MEquipmentStatus->getById($getFirstPenimbanganOn['id_equipment_status']);
                $timeOn = $penimbanganOn['date_equipment'] . " " . $penimbanganOn['time_equipment'];

                $getLastPenimbanganOff = $this->MEquipmentStatus->getLastPenimbanganOff($no_batch);
                $penimbanganOff = $this->MEquipmentStatus->getById($getLastPenimbanganOff['id_equipment_status']);
                $timeOff = $penimbanganOff['date_equipment'] . " " . $penimbanganOff['time_equipment'];

                $feedingTime1 = new DateTime(date("H:i:s", strtotime($timeOn)));
                $feedingTime2 = new DateTime(date("H:i:s", strtotime($timeOff)));
                $feedingTimeDiff = $feedingTime1->diff($feedingTime2);
                $totalFeedingTime->add($feedingTimeDiff);

                $intervalFeedingTime = $cloneTotalFeedingTime->diff($totalFeedingTime);

                $intervalTotalFeedingTime = sprintf(
                    "%02d:%02d:%02d",
                    $intervalFeedingTime->h + ($intervalFeedingTime->d * 24), // jika interval lebih dari 1 hari, jam harus ditambah
                    $intervalFeedingTime->i,
                    $intervalFeedingTime->s
                );

                // Kurangi Mixing Time
                $resFeedingTime = strtotime("1970-01-01 " . $intervalTotalFeedingTime);
                $resMaterialTime = strtotime("1970-01-01 " . $intervalTotalMaterial);

                $delayTime = $resFeedingTime - $resMaterialTime;
                $resultDelayTime = gmdate("H:i:s", abs($delayTime));
                // End of delay time

                // Cycle Time
                // Konversi ke detik
                list($h1, $m1, $s1) = explode(":", $resultTimeFormat);
                list($h2, $m2, $s2) = explode(":", $resultDelayTime);

                $scndEquipmentTime = $h1 * 3600 + $m1 * 60 + $s1;
                $scndDelayTime = $h2 * 3600 + $m2 * 60 + $s2;

                // Jumlahkan
                $cycleTime = $scndEquipmentTime + $scndDelayTime;

                $resultCycleTime = gmdate("H:i:s", abs($cycleTime));

                $response = [
                    'code' => 200,
                    'status' => 'ok',
                    'msg' => 'Data fetched',
                    'totalEquipmentTime' => $resultCycleTime,
                    'totalMaterialTime' => $intervalTotalMaterial,
                    'totalFeedingTime' => $intervalTotalFeedingTime,
                    'totalDelayTime' => $resultDelayTime,
                    'product' => $kode_product,
                    'dataEquipment' => $rekapEquipment,
                    'dataTimbang' => $getTimbangWithResep,
                    'dataProduct' => $getProduct,
                ];

                return $this->output->set_output(json_encode($response));
            } else {
                $response = [
                    'code' => 401,
                    'status' => 'ok',
                    'msg' => 'No batch cannot be null'
                ];

                return $this->output->set_output(json_encode($response));
            }
        } else {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Method not found',
            ];

            return $this->output->set_output(json_encode($response));
        }
    }

    function format_interval(DateInterval $interval)
    {
        $result = "";
        if ($interval->y) {
            $result .= $interval->format("%y yr ");
        }
        if ($interval->m) {
            $result .= $interval->format("%m mnth ");
        }
        if ($interval->d) {
            $result .= $interval->format("%d days ");
        }
        if ($interval->h) {
            $result .= $interval->format("%h hrs ");
        }
        if ($interval->i) {
            $result .= $interval->format("%i mnt ");
        }
        if ($interval->s) {
            $result .= $interval->format("%s scnd ");
        }

        return $result;
    }

    function formatWaktuToSingkat($timeString)
    {
        list($jam, $menit, $detik) = explode(":", $timeString);
        $jam = (int)$jam;
        $menit = (int)$menit;
        $detik = (int)$detik;

        // Ubah semua ke menit & detik saja
        $totalMenit = ($jam * 60) + $menit;

        $result = [];

        if ($totalMenit > 0) {
            $result[] = $totalMenit . " mnt";
        }

        if ($detik > 0 || empty($result)) {
            $result[] = $detik . " scnd";
        }

        return implode(" ", $result);
    }
}
