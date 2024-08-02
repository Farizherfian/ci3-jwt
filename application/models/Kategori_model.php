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
    public function ubah_kat_supplier($id_kat_supplier,$data)
	{
		$this->db->where('id_kat_supplier',$id_kat_supplier);
		return $this->db->update('kategori_supplier',$data);
	}
	public function hapus_kat_supplier($id_kat_supplier)
    {
        $this->db->where('id_kat_supplier', $id_kat_supplier);
        return $this->db->delete('kategori_supplier');
    }
    public function cek_kategori1($id_kat_supplier)
    {
    	return $this->db->get_where('kategori_supplier', array('id_kat_supplier' => $id_kat_supplier))->num_rows() > 0;
    }
}



 ?>