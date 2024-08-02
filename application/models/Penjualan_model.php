<?php

class Penjualan_model extends CI_Model
{
    // public function tambah_penjualan($data_penjualan, $data_detail_penjualan)
    // {
    //     $this->db->trans_start();

    //     $this->db->insert('penjualan', $data_penjualan);
    //     $id_penjualan = $this->db->insert_id();

    //     foreach ($data_detail_penjualan as &$detail) {
    //         $detail['id_penjualan'] = $id_penjualan;
    //     }
    //     $this->db->insert_batch('penjualan_detail', $data_detail_penjualan);


    //     $this->db->trans_complete();

    //     return $this->db->trans_status();
    // }
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
//}
}
