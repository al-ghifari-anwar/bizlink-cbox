<?php

class Emergency extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('MEmergency');
        $this->load->model('MaxchatHelper');
        $this->load->model('MUser');
        $this->load->model('MLogMsg');
    }

    public function save()
    {
        $this->output->set_content_type('application/json');

        $post = json_decode(file_get_contents('php://input'), true) != null ? json_decode(file_get_contents('php://input'), true) : $this->input->post();

        $no_batch = $post['no_batch'];
        $date_emergency = $post['date_emergency'];
        $time_emergency = $post['time_emergency'];
        $status_emergency = $post['status'];
        $id_spk = $post['id_spk'];
        $id_trans = $post['id_trans'];

        $emergencyData = [
            'no_batch' => $no_batch,
            'date_emergency' => $date_emergency,
            'time_emergency' => $time_emergency,
            'status_emergency' => $status_emergency,
            'id_spk' => $id_spk,
            'id_trans' => $id_trans,
        ];

        $save = $this->MEmergency->create($emergencyData);

        if (!$save) {
            $response = [
                'code' => 401,
                'status' => 'failed',
                'msg' => 'Not saved',
            ];

            return $this->output->set_output(json_encode($response));
        } else {
            $users = $this->MUser->get();

            foreach ($users as $user) {
                if ($status_emergency == 'ESTOP-ON') {
                    $messageContent = "Emergency! Emergency Stop untuk batch " . $no_batch;
                } else {
                    $messageContent = "Terjadi Overload! batch " . $no_batch . " pada equipment " . $status_emergency;
                }

                // Send WA
                $nomor_hp = $user['phone_user'];
                $nama = $user['name_user'];
                $message = $messageContent;
                $full_name = "Miraswift";

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
                    'type_msg' => 'emercency',
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
    }
}
