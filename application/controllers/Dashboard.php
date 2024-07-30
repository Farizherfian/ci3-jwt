<?php 


class Dashboard extends CI_Controller
{
    
    public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
    }
    public function index()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }
        $data = array(
            'jumlah_kategori' => $this->db->get('kategori_barang')->num_rows(),
            'jumlah_barang' => $this->db->get('barang')->num_rows()
        );
        return $this->sendJson(array("status" => true, "data" => $data));
    }
    private function verify_token()
    {
        // $headers = $this->input->request_headers();
        // if (isset($headers['Authorization'])) {
        //  $decodedToken = $this->authorization_token->validateToken($headers['Authorization']);
        //  if ($decodedToken['status']) {
        //      return true;
        //  }
        // }
        // return false;
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']); // Hapus prefix "Bearer "

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

}
 ?>