<?php

class Penjualan_model extends CI_Model
{
    public function tambah_penjualan($penjualan)
    {
        $this->db->insert('penjualan', $penjualan);
        return $this->db->insert_id();
    }

    public function tambah_detail_penjualan($detail)
    {
        $this->db->insert('penjualan_detail', $detail);
    }

    public function update_total_penjualan($id_penjualan, $total)
    {
        $this->db->where('id_penjualan', $id_penjualan);
        $this->db->update('penjualan', ['total' => $total]);
    }
    public function get_penjualan_by_id($id_penjualan)
    {
        $this->db->select('penjualan.*, penjualan_detail.id_barang, penjualan_detail.jumlah, penjualan_detail.harga, penjualan_detail.subtotal, barang.nama as nama_barang');
        $this->db->from('penjualan');
        $this->db->join('penjualan_detail', 'penjualan.id_penjualan = penjualan_detail.id_penjualan');
        $this->db->join('barang', 'penjualan_detail.id_barang = barang.id_barang');
        $this->db->where('penjualan.id_penjualan', $id_penjualan);
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_penjualan_details($id_penjualan)
    {
        $this->db->select('penjualan_detail.*');
        $this->db->from('penjualan_detail');
        $this->db->where('id_penjualan', $id_penjualan);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function delete_penjualan($id_penjualan)
    {
        $this->db->trans_start();

        $details = $this->get_penjualan_details($id_penjualan);

        foreach ($details as $detail) {
            $this->db->set('stok', 'stok+' . (int)$detail['jumlah'], FALSE);
            $this->db->where('id_barang', $detail['id_barang']);
            $this->db->update('barang');
        }

        $this->db->delete('penjualan_detail', array('id_penjualan' => $id_penjualan));
        $this->db->delete('penjualan', array('id_penjualan' => $id_penjualan));

        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
