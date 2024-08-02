<?php 

class Kategori_supplier extends CI_Controller
{
	
	public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
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

        $kategori = $this->db->get('kategori_supplier')->result_array();
        return $this->sendJson(array("status" => true, "data" => $kategori));
    }
    public function tambah()
    {
    	if (!$this->verify_token()) {
    		return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    	}

    	$data = array(
        	'nama' => $this->input->post('nama')
    	);

    	$this->form_validation->set_data($data);
   	 	$this->form_validation->set_rules('nama', 'Kategori Supplier', 'required');

    	if ($this->form_validation->run() == false) {
    		return $this->sendJson(array("error" => validation_errors()));
    	}else{
    		$this->db->insert('kategori_supplier',$data);
    		return $this->sendJson(array('status' => true, 'message' => "Data berhasil ditambahkan" ));
    	}
    }
    public function ubah($id_kat_supplier)
	{
    	if (!$this->verify_token()) {
        	return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    	}

    	$data = array(
        	'nama' => $this->input->post('nama')
    	);


    	$this->form_validation->set_data($data);
    	$this->form_validation->set_rules('nama', 'Kategori Supplier', 'required');

    	if ($this->form_validation->run() == FALSE) {
        return $this->sendJson(array("status" => false, "message" => validation_errors()));
    	}
    	if ($this->Kategori_model->ubah_kat_supplier($id_kat_supplier, $data)) {
        	return $this->sendJson(array("status" => true, "message" => "Ubah Barang Berhasil"));
    	} else {
        	return $this->sendJson(array("status" => false, "message" => "Ubah Barang Gagal"));
    	}
	}
    public function hapus($id_kat_supplier)
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        if ($this->Kategori_model->hapus_kat_supplier($id_kat_supplier)) {
            return $this->sendJson(array("status" => true, "message" => "Barang Berhasil dihapus"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Barang Gagal dihapus"));
        }
    }
    public function hitung()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $data = $this->db->get('kategori_supplier')->num_rows();

        return $this->sendJson(array('status' => true, 'data' => $data));
    }
}



?>