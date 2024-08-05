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
    public function index()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $penjualan = $this->db->get('penjualan')->result();
        return $this->sendJson(array("status" => true, "data" => $penjualan));
    }

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
                if ($barang['stok'] < $item['jumlah']) {
                    return $this->sendJson(array("status" => false, "message" => "Stok barang dengan ID " . $item['id_barang'] . " tidak mencukupi"));
                }
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
            }else {
                 return $this->sendJson(array("status" => false, "message" => "Barang dengan ID " . $item['id_barang'] . " tidak ditemukan"));
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

    public function detail($id_penjualan)
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $penjualan_detail = $this->Penjualan_model->get_penjualan_by_id($id_penjualan);

        if ($penjualan_detail) {
            return $this->sendJson(array("status" => true, "data" => $penjualan_detail));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Penjualan tidak ditemukan"));
        }
    }
    public function hapus($id_penjualan)
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        if ($this->Penjualan_model->delete_penjualan($id_penjualan)) {
            return $this->sendJson(array("status" => true, "message" => "Penjualan berhasil dihapus"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Penjualan gagal dihapus"));
        }
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
