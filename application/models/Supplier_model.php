<?php 

class Supplier_model extends CI_model
{
	public function tampil()
	{
		$this->db->select('supplier.*, kategori_supplier.nama as nama_kategori');
		$this->db->from('supplier');
		$this->db->join('kategori_supplier', 'kategori_supplier.id_kat_supplier = supplier.id_kat_supplier', 'left');
		return $this->db->get()->result();
	}
	public function ubah($id_supplier,$data)
	{
		$this->db->where('id_supplier',$id_supplier);
		return $this->db->update('supplier',$data);
	}
	public function hapus($id_supplier)
    {
        $this->db->where('id_supplier',$id_supplier);
		return $this->db->delete('supplier');
    }
    public function cek_supplier($id_supplier)
    {
        return $this->db->get_where('supplier', array('id_supplier' => $id_supplier))->num_rows() > 0;
    }
}



 ?>