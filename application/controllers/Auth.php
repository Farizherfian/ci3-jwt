<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

    public function __construct($config = "rest")
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Content-Length, Accept-Encoding,Authorization");
        parent::__construct();
        $this->load->model('Auth_model');
    }
    public function register()
    {
        $this->form_validation->set_rules('email','Email','required|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('username','Username','required');
        $this->form_validation->set_rules('password','Password','required|matches[konfirmasi_password]');
        $this->form_validation->set_rules('konfirmasi_password','Konfirmasi Password','required|matches[password]');

        if ($this->form_validation->run() != false) {
            $data = array(
                'email' => $this->input->post('email'),
                'username' => $this->input->post('username'),
                'password' => password_hash($this->input->post('password'), PASSWORD_DEFAULT),
            );
            $this->db->insert('user',$data);
            return $this->sendJson(array("status" => true,"message" => "Register Success!"));
        }else{
            return $this->sendJson(array("error" => validation_errors()));
        }
    }
    public function login()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == false) {
            return $this->sendJson(array("error" => validation_errors()));
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');

            $user = $this->db->get_where('user', ['email' => $email])->row_array();

            if ($user && password_verify($password, $user['password'])) {
                $token_data['userEmail'] = $email;
                $tokenData = $this->authorization_token->generateToken($token_data);
                return $this->sendJson(array("token" => $tokenData, "status" => true, "response" => "Login Success!"));
            } else {
                return $this->sendJson(array("status" => false, "message" => "Email atau Password Salah!"));
            }
        }

    }
    public function logout()
    {
        $headers = $this->input->request_headers();
        if (isset($headers['Authorization'])) {
            $token = str_replace('Bearer ', '', $headers['Authorization']);

            // Simpan token ke database
            $this->db->insert('blok_token', ['token' => $token]);

            return $this->sendJson(array("status" => true, "message" => "Logout Success!"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Authorization Header Not Found"));
        }
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
    public function profil()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $user = $this->Auth_model->get_user_by_email($this->email);
        if ($user) {
            unset($user['password']);
            return $this->sendJson(array("status" => true, "user" => $user));
        } else {
            return $this->sendJson(array("status" => false, "message" => "User not found"));
        }
    }
    public function update_profil()
    {
        if (!$this->verify_token()) {
            return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
        }

        $this->form_validation->set_rules('username', 'Username', 'required');

        if ($this->input->post('new_password')) {
            $this->form_validation->set_rules('password', 'Password lama', 'required');
            $this->form_validation->set_rules('new_password', 'New Password', 'required');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');
        }

        if ($this->form_validation->run() == false) {
            return $this->sendJson(array("status" => false, "error" => validation_errors()));
        }

        $username = $this->input->post('username');
        $password = $this->input->post('password');
        $new_password = $this->input->post('new_password');

        $user = $this->Auth_model->get_user_by_email($this->email);
        if (!$user) {
            return $this->sendJson(array("status" => false, "message" => "User tidak ada"));
        }
        
        $data = array(
            'username' => $username
        );

        if (!empty($new_password)) {
            if (!password_verify($password, $user['password'])) {
                return $this->sendJson(array("status" => false, "message" => "password salah"));
            }
            $data['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }


        if ($this->Auth_model->update_user($this->email, $data)) {
            return $this->sendJson(array("status" => true, "message" => "Update profil berhasil"));
        } else {
            return $this->sendJson(array("status" => false, "message" => "Update profil gagal"));
        }
    }
    private function sendJson($data)
    {
        $this->output->set_header('Content-Type: application/json; charset=utf-8')->set_output(json_encode($data));
    }

}









    // public function ubah_password()
    // {
    //     if (!$this->verify_token()) {
    //         return $this->sendJson(array("status" => false, "message" => "Invalid Token"));
    //     }

    //     $this->form_validation->set_rules('password_lama', 'Password Lama', 'required');
    //     $this->form_validation->set_rules('password_baru', 'Password Baru', 'required');
    //     $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password_baru]');

    //     if ($this->form_validation->run() == false) {
    //         return $this->sendJson(array("status" => false, "message" => validation_errors()));
    //     }

    //     $password_lama = $this->input->post('password_lama');
    //     $password_baru = $this->input->post('password_baru');

    //     $user = $this->db->get_where('user', ['email' => $this->email])->row_array();

    //     if ($user && password_verify($password_lama, $user['password'])) {
    //         if ($this->Auth_model->update_password($this->email, $password_baru)) {
    //             return $this->sendJson(array("status" => true, "message" => "Password berhasil diubah"));
    //         } else {
    //             return $this->sendJson(array("status" => false, "message" => "Password gagal diubah"));
    //         }
    //     } else {
    //         return $this->sendJson(array("status" => false, "message" => "Password lama tidak sesuai!"));
    //     }
    // }

    // public function login()
    // {
    //     if ($this->input->method() === 'post') {
    //         $email = $this->input->post('email');
    //         $password = $this->input->post('password');

    //         if ($email == "test@mail.com" and $password == "test") {
    //             $token_data['userEmail'] = $email;
    //             // $token_data['userRole'] = "Admin";
    //             $tokenData = $this->authorization_token->generateToken($token_data);
    //             return $this->sendJson(array("token" => $tokenData, "status" => true, "response" => "Login Success!"));
    //         } else {
    //             return $this->sendJson(array("token" => null, "status" => false, "response" => "Login Failed!"));
    //         }
    //     } else {
    //         return $this->sendJson(array("message" => "POST Method", "status" => false));
    //     }
    // }

    // private function sendJson($data)
    // {
    //     $this->output->set_header('Content-Type: application/json; charset=utf-8')->set_output(json_encode($data));
    // }
