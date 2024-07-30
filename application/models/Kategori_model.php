<?php 

class Kategori_model extends CI_model
{
	public function tampil()
	{
		return $this->get('kategori_barang')->result();
	}
	public function ubah($id_kategori,$data)
	{
		$this->db->where('id_kategori',$id_kategori);
		return $this->db->update('kategori_barang',$data);
	}
	public function hapus($id_kategori)
    {
        $this->db->where('id_kategori', $id_kategori);
        return $this->db->delete('kategori_barang');
    }
    public function cek_kategori($id_kategori)
    {
    	return $this->db->get_where('kategori_barang', array('id_kategori' => $id_kategori))->num_rows() > 0;
    }
}



 ?>