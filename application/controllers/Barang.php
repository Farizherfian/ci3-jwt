<?php 

class Barang extends CI_Controller
{
	
	public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
        $this->load->model('Barang_model');
        $this->load->model('Kategori_model');
        $this->load->model('Supplier_model');
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
    private function sendJson($data)
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8')->set_output(json_encode($data));
    }
    public function index()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $barang = $this->Barang_model->tampil();
        return $this->sendJson(array("status" => true, "data" => $barang));
    }
    public function tambah()
    {
    	if (!$this->verify_token()) {
    		return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    	}

    	$data = array(
    		'id_supplier' => $this->input->post('id_supplier'),
        	'id_kategori' => $this->input->post('id_kategori'),
        	'nama' => $this->input->post('nama'),
        	'harga' => $this->input->post('harga'),
        	'stok' => $this->input->post('stok')
    	);

    	$this->form_validation->set_data($data);
    	$this->form_validation->set_rules('id_supplier', 'Supplier', 'required|callback_cek_id_supplier');
   	 	$this->form_validation->set_rules('id_kategori', 'Kategori', 'required|callback_cek_id_kategori');
   	 	$this->form_validation->set_rules('nama', 'Nama', 'required');
    	$this->form_validation->set_rules('harga', 'Harga', 'required|greater_than[0]');
    	$this->form_validation->set_rules('stok', 'Stok', 'required|greater_than_equal_to[0]');

    	if ($this->form_validation->run() == false) {
    		return $this->sendJson(array("error" => validation_errors()));
    	}else{
    		$this->db->insert('barang',$data);
    		return $this->sendJson(array('status' => true, 'message' => "Tambah Barang Berhasil" ));
    	}
    }
    public function ubah($id_barang)
	{
    	if (!$this->verify_token()) {
        	return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    	}

    	// $data = array(
     //    	'id_supplier' => $this->input->post('id_supplier'),
     //    	'id_kategori' => $this->input->post('id_kategori'),
     //    	'nama' => $this->input->post('nama'),
     //    	'harga' => $this->input->post('harga'),
    	// );
        //Mendapatkan data mentah dari request PUT
    $raw_data = $this->input->raw_input_stream;
    //Menguraikan data mentah ke array asosiatif
    $data = json_decode($raw_data, true);


    	$this->form_validation->set_data($data);
    	$this->form_validation->set_rules('id_supplier', 'Supplier', 'required|callback_cek_id_supplier');
    	$this->form_validation->set_rules('id_kategori', 'Kategori Barang', 'required|callback_cek_id_kategori');
    	$this->form_validation->set_rules('nama', 'Nama', 'required');
    	$this->form_validation->set_rules('harga', 'Harga', 'required|greater_than[0]');

    	if ($this->form_validation->run() == FALSE) {
        return $this->sendJson(array("status" => false, "message" => validation_errors()));
    	}
    	if ($this->Barang_model->ubah($id_barang, $data)) {
        	return $this->sendJson(array("status" => true, "message" => "Ubah Barang Berhasil"));
    	} else {
        	return $this->sendJson(array("status" => false, "message" => "Ubah Barang Gagal"));
    	}
	}
	public function detail($id_barang)
	{
		if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $barang = $this->Barang_model->detail($id_barang);
        if ($barang) {
        	return $this->sendJson(array("status" => true, "data" => $barang));
        }else{
            return $this->sendJson(array("status" => false, "message" => "Barang tidak ada"));
        }
	}
    public function cek_id_kategori($id_kategori)
    {
    	if ($this->Kategori_model->cek_kategori($id_kategori)) {
            return true;
        } else {
            $this->form_validation->set_message('cek_id_kategori', 'The {field} does not exist.');
            return false;
        }
    }
    public function cek_id_supplier($id_supplier)
    {
    	if ($this->Supplier_model->cek_supplier($id_supplier)) {
            return true;
        } else {
            $this->form_validation->set_message('cek_id_kategori', 'The {field} does not exist.');
            return false;
        }
    }
    public function hitung_barang()
    {
    	if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }
        $data = $this->db->get('barang')->num_rows();
       
        return $this->sendJson(array("status" => true, "data" => $data));
    }
    public function hapus($id_barang)
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        if ($this->Barang_model->hapus($id_barang)) {
            return $this->sendJson(array("status" => true, "message" => "Barang Berhasil dihapus"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Barang Gagal dihapus"));
        }
    }
}



?>