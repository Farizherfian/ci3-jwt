<?php

class BarangMasuk_model extends CI_Model
{
    // Fungsi untuk menambah data barang masuk
    public function tambah_barang_masuk($data)
    {
        $this->db->trans_start();

        // Menambah data ke tabel barang_masuk
        $this->db->insert('barang_masuk', $data);

        // Mengupdate stok barang di tabel barang
        // $this->db->set('stok', 'stok + ' . (int)$data['jumlah'], FALSE);
        // $this->db->where('id_barang', $data['id_barang']);
        // $this->db->update('barang');

        $this->db->trans_complete();

        return $this->db->trans_status();
    }

    // Fungsi untuk mendapatkan daftar barang masuk
    public function tampil()
    {
        $this->db->select('barang_masuk.*, barang.nama as nama_barang,supplier.nama as nama_supplier');
        $this->db->from('barang_masuk');
        $this->db->join('barang', 'barang.id_barang = barang_masuk.id_barang', 'left');
        $this->db->join('supplier', 'supplier.id_supplier = barang_masuk.id_supplier', 'left');
        $query = $this->db->get();

        return $query->result_array();
    }

    // Fungsi untuk mendapatkan data barang masuk berdasarkan id_barang_masuk
    // public function get_barang_masuk_by_id($id_barang_masuk)
    // {
    //     $this->db->select('barang_masuk.*, barang.nama as nama_barang');
    //     $this->db->from('barang_masuk');
    //     $this->db->join('barang', 'barang.id_barang = barang_masuk.id_barang', 'left');
    //     $this->db->where('id_barang_masuk', $id_barang_masuk);
    //     $query = $this->db->get();

    //     return $query->row_array();
    // }
}
