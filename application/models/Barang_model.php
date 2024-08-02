<?php 

class Barang_model extends CI_model
{
	public function tampil()
	{
		$this->db->select('barang.*, kategori_barang.nama as nama_kategori, supplier.nama as nama_supplier');
		$this->db->from('barang');
		$this->db->join('kategori_barang', 'kategori_barang.id_kategori = barang.id_kategori', 'left');
		$this->db->join('supplier', 'supplier.id_supplier = barang.id_supplier', 'left');
		return $this->db->get()->result();
	}
	public function ubah($id_barang,$data)
	{
		$this->db->where('id_barang',$id_barang);
		return $this->db->update('barang',$data);
	}
	public function detail($id_barang)
	{
		$this->db->select('barang.*, kategori_barang.nama as nama_kategori');
		$this->db->from('barang');
		$this->db->join('kategori_barang', 'kategori_barang.id_kategori = barang.id_kategori', 'left');
		$this->db->where('barang.id_barang',$id_barang);
		return $this->db->get()->row_array();
	}
	public function hapus($id_barang)
    {
        $this->db->where('id_barang', $id_barang);
        $query = $this->db->get('barang');
        if ($query->num_rows() > 0) {
        	$this->db->where('id', $id_barang);
            $this->db->delete('barang');
            return true;
        }else{
        	return false;
        }
    }
    public function get_barang_by_id($id_barang) {
        return $this->db->get_where('barang', array('id_barang' => $id_barang))->row_array();
    }

    public function update_stok_barang($id_barang, $jumlah) {
        $this->db->set('stok', 'stok + ' . (int)$jumlah, FALSE);
        $this->db->where('id_barang', $id_barang);
        $this->db->update('barang');
    }
    public function update_stok($id_barang, $jumlah)
    {
        $this->db->set('stok', 'stok+' . $jumlah, FALSE);
        $this->db->where('id_barang', $id_barang);
        $this->db->update('barang');
    }
}



 ?>