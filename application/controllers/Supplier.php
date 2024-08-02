<?php 

class Supplier extends CI_Controller
{
	
	public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
        $this->load->model('Supplier_model');
        $this->load->model('Kategori_model');
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

        $supplier = $this->Supplier_model->tampil();
        return $this->sendJson(array("status" => true, "data" => $supplier));
    }
    public function tambah()
    {
    	if (!$this->verify_token()) {
    		return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    	}

    	$data = array(
        	'id_kat_supplier' => $this->input->post('id_kat_supplier'),
            'nama' => $this->input->post('nama'),
        	'no_hp' => $this->input->post('no_hp'),
        	'alamat' => $this->input->post('alamat')
    	);

    	$this->form_validation->set_data($data);
   	 	$this->form_validation->set_rules('id_kat_supplier', 'Kategori Supplier', 'required|callback_cek_id_kat_supplier');
        $this->form_validation->set_rules('nama', 'Nama', 'required');
    	$this->form_validation->set_rules('no_hp', 'No HP', 'required');
    	$this->form_validation->set_rules('alamat', 'Alamat', 'required');

    	if ($this->form_validation->run() == false) {
    		return $this->sendJson(array("error" => validation_errors()));
    	}else{
    		$this->db->insert('supplier',$data);
    		return $this->sendJson(array('status' => true, 'message' => "Tambah Supplier Berhasil" ));
    	}
    }
 //    public function ubah($id_supplier)
	// {
 //    	if (!$this->verify_token()) {
 //        	return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
 //    	}

 //    	$data = array(
 //            'id_kat_supplier' => $this->input->post('id_kat_supplier'),
 //            'nama' => $this->input->post('nama'),
 //            'no_hp' => $this->input->post('no_hp'),
 //            'alamat' => $this->input->post('alamat')
 //        );

 //        $this->form_validation->set_data($data);
 //        $this->form_validation->set_rules('id_kat_supplier', 'Kategori Supplier', 'required|callback_cek_id_kat_supplier');
 //        $this->form_validation->set_rules('nama', 'Nama', 'required');
 //        $this->form_validation->set_rules('no_hp', 'No HP', 'required');
 //        $this->form_validation->set_rules('alamat', 'Alamat', 'required');

 //    	if ($this->form_validation->run() == FALSE) {
 //        return $this->sendJson(array("status" => false, "message" => validation_errors()));
 //    	}
 //    	if ($this->Supplier_model->ubah($id_supplier, $data)) {
 //        	return $this->sendJson(array("status" => true, "message" => "Ubah Barang Berhasil"));
 //    	} else {
 //        	return $this->sendJson(array("status" => false, "message" => "Ubah Barang Gagal"));
 //    	}
	// }
    public function ubah($id_supplier)
{
    if (!$this->verify_token()) {
        return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    }

    //Mendapatkan data mentah dari request PUT
    $raw_data = $this->input->raw_input_stream;
    //Menguraikan data mentah ke array asosiatif
    $data = json_decode($raw_data, true);

    //Mengecek apakah data ada dan valid
    if (!$data) {
        return $this->sendJson(array("status" => false, "message" => "Invalid data"));
    }

    $this->form_validation->set_data($data);
    $this->form_validation->set_rules('id_kat_supplier', 'Kategori Supplier', 'required|callback_cek_id_kat_supplier');
    $this->form_validation->set_rules('nama', 'Nama', 'required');
    $this->form_validation->set_rules('no_hp', 'No HP', 'required');
    $this->form_validation->set_rules('alamat', 'Alamat', 'required');

    if ($this->form_validation->run() == FALSE) {
        return $this->sendJson(array("status" => false, "message" => validation_errors()));
    }
    if ($this->Supplier_model->ubah($id_supplier, $data)) {
        return $this->sendJson(array("status" => true, "message" => "Ubah Barang Berhasil"));
    } else {
        return $this->sendJson(array("status" => false, "message" => "Ubah Barang Gagal"));
    }
}

    public function hitung_supplier()
    {
    	if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }
        $data = $this->db->get('supplier')->num_rows();
       
        return $this->sendJson(array("status" => true, "data" => $data));
    }
    public function hapus($id_supplier)
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        if ($this->Supplier_model->hapus($id_supplier)) {
            return $this->sendJson(array("status" => true, "message" => "Barang Berhasil dihapus"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Barang Gagal dihapus"));
        }
    }
    public function cek_id_kat_supplier($id_kat_supplier)
    {
        if ($this->Kategori_model->cek_kategori1($id_kat_supplier)) {
            return true;
        } else {
            $this->form_validation->set_message('cek_id_kat_supplier', 'The {field} does not exist.');
            return false;
        }
    }
}



?>