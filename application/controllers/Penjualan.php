<?php

class Penjualan extends CI_Controller
{
    public function __construct($config = "rest") {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization");
        parent::__construct();
        $this->load->model('Penjualan_model');
        $this->load->model('Barang_model');
    }

// public function tambah() {
//     if (!$this->verify_token()) {
//         return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
//     }

    
//     $input_data = json_decode(file_get_contents('php://input'), true);

//     if (!isset($input_data['detail']) || !is_array($input_data['detail'])) {
//         return $this->sendJson(array("status" => false, "message" => "Data detail tidak valid"));
//     }

//     $data_detail = $input_data['detail'];

//     $data_penjualan = array(
//         'tanggal' => date('Y-m-d H:i:s'),
//         'total_harga' => 0
//     );

//     // Hitung total harga
//     $total_harga = 0;
//     foreach ($data_detail as &$detail) {
//         $barang = $this->db->get_where('barang', array('id_barang' => $detail['id_barang']))->row_array();
//         if ($barang) {
//             $detail['harga'] = $barang['harga'];
//             $total_harga += $barang['harga'] * $detail['jumlah'];
//             $this->db->set('stok', 'stok - ' . (int)$detail['jumlah'], FALSE);
//             $this->db->where('id_barang', $detail['id_barang']);
//             $this->db->update('barang');
//         } else {
//             return $this->sendJson(array("status" => false, "message" => "Barang dengan ID " . $detail['id_barang'] . " tidak ditemukan"));
//         }
//     }

//     $data_penjualan['total_harga'] = $total_harga;

//     $id_penjualan = $this->Penjualan_model->tambah_penjualan($data_penjualan, $data_detail);

//     if ($id_penjualan) {
//         return $this->sendJson(array("status" => true, "message" => "Penjualan berhasil ditambahkan", "total_harga" => $total_harga, "id_penjualan" => $id_penjualan));
//     } else {
//         return $this->sendJson(array("status" => false, "message" => "Penjualan gagal ditambahkan"));
//     }
// }
    public function tambah()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }
        
        $input = json_decode($this->input->raw_input_stream, true);
        $penjualan = [
            'tanggal' => date('Y-m-d H:i:s'),
            'total' => 0
        ];
        $penjualan_id = $this->Penjualan_model->tambah_penjualan($penjualan);
        $total = 0;

        foreach ($input['items'] as $item) {
            $barang = $this->Barang_model->get_barang_by_id($item['id_barang']);
            if ($barang) {
                $subtotal = $barang['harga'] * $item['jumlah'];
                $detail = [
                    'id_penjualan' => $penjualan_id,
                    'id_barang' => $item['id_barang'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $barang['harga'],
                    'subtotal' => $subtotal
                ];
                $this->Penjualan_model->tambah_detail_penjualan($detail);
                $this->Barang_model->update_stok($item['id_barang'], -$item['jumlah']);
                $total += $subtotal;
            }
        }

        $this->Penjualan_model->update_total_penjualan($penjualan_id, $total);

        $response = [
            'status' => true,
            'message' => 'Penjualan berhasil ditambahkan',
            'total' => $total
        ];
        return $this->sendJson($response);
    }


    private function sendJson($data)
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8')->set_output(json_encode($data));
    }

    private function verify_token()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']); 

            // Periksa apakah token ada dalam tabel blocked_tokens
            $token_exists = $this->db->get_where('blok_token', ['token' => $token])->row_array();
            if ($token_exists) {
                return false;
            }

            $decodedToken = $this->authorization_token->validateToken($token);
            if ($decodedToken['status']) {
                 $this->email = $decodedToken['data']->userEmail;
                return true;
            }
        }
        return false;
    }
}
