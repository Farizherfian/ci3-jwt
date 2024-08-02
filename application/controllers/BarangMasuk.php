<?php

class BarangMasuk extends CI_Controller {

    public function __construct($config = "rest") {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding, Authorization");
        parent::__construct();
        $this->load->model('BarangMasuk_model');
        $this->load->model('Barang_model'); 
        $this->load->model('Supplier_model');
    }
    public function index()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $masuk = $this->BarangMasuk_model->tampil();
        return $this->sendJson(array("status" => true, "data" => $masuk));
    }

    public function tambah() {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $data = array(
            'id_supplier' => $this->input->post('id_supplier'),
            'id_barang' => $this->input->post('id_barang'),
            'jumlah' => $this->input->post('jumlah')
        );

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('id_supplier', 'Supplier', 'required|callback_valid_supplier');
        $this->form_validation->set_rules('id_barang', 'Barang', 'required|callback_valid_barang');
        $this->form_validation->set_rules('jumlah', 'Jumlah', 'required|greater_than_equal_to[1]');

        if ($this->form_validation->run() == false) {
            return $this->sendJson(array("error" => validation_errors()));
        } else {
            // Mulai transaksi database
            $this->db->trans_start();
            
            // Tambah data barang masuk
            $this->BarangMasuk_model->tambah_barang_masuk($data);

            // Update stok barang
            $this->Barang_model->update_stok_barang($data['id_barang'], $data['jumlah']);

            // Selesaikan transaksi
            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                return $this->sendJson(array("status" => false, "message" => "Gagal menambahkan barang masuk"));
            } else {
                return $this->sendJson(array("status" => true, "message" => "Barang masuk berhasil ditambahkan"));
            }
        }
    }

    public function valid_barang($id_barang) {
        $barang = $this->Barang_model->get_barang_by_id($id_barang);
        if ($barang) {
            return true;
        } else {
            $this->form_validation->set_message('valid_barang', 'ID Barang tidak valid');
            return false;
        }
    }

    public function valid_supplier($id_supplier) {
        $supplier = $this->Supplier_model->cek_supplier($id_supplier);
        if ($supplier) {
            return true;
        } else {
            $this->form_validation->set_message('valid_supplier', 'ID Supplier tidak valid');
            return false;
        }
    }

    private function sendJson($data) {
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
?>
