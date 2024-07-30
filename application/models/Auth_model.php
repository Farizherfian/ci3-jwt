<?php 

class Auth_model extends CI_model
{
	// public function update_password($email, $password_baru)
 //    {
 //        $this->db->where('email', $email);
 //        return $this->db->update('user', ['password' => password_hash($password_baru, PASSWORD_DEFAULT)]);
 //    }
    public function get_user_by_email($email)
    {
        return $this->db->get_where('user', ['email' => $email])->row_array();
    }

    public function update_user($email, $data)
    {
        $this->db->where('email', $email);
        return $this->db->update('user', $data);
    }
}



 ?>