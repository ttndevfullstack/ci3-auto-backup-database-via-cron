<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup_model extends CI_Model {
    
    public function get_settings() {
        return $this->db->get('backup_settings')->row();
    }

    public function update_settings($data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($this->db->get('backup_settings')->num_rows() > 0) {
            return $this->db->update('backup_settings', $data);
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('backup_settings', $data);
    }

    public function create_backup_history($filename, $size) {
        $data = [
            'filename' => $filename,
            'size' => $size,
            'status' => 'processing',
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('backup_history', $data);
        return $this->db->insert_id();
    }

    public function update_backup_status($id, $status, $s3_url = null) {
        $data = ['status' => $status];
        if ($s3_url) {
            $data['s3_url'] = $s3_url;
        }
        return $this->db->where('id', $id)->update('backup_history', $data);
    }

    public function get_backup_history() {
        return $this->db->order_by('created_at', 'DESC')
                       ->get('backup_history')
                       ->result();
    }
}
