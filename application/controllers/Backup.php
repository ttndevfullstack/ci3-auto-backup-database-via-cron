<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->library('backup_library');
        $this->load->model('backup_model');
        $this->load->helper(['url', 'form']);
    }

    public function index() {
        $data['settings'] = $this->backup_library->get_backup_settings();
        $data['history'] = $this->backup_model->get_backup_history();
        $this->load->view('backup/index', $data);
    }

    public function settings() {
        if ($this->input->post()) {
            $this->backup_library->update_backup_settings([
                'backup_time' => $this->input->post('backup_time'),
                'max_local_backups' => $this->input->post('max_local_backups'),
                'enabled' => (int)$this->input->post('enabled', true)
            ]);
            redirect('backup');
        }
        
        $data['settings'] = $this->backup_library->get_backup_settings();
        $this->load->view('backup/settings', $data);
    }

    public function execute(): void {
        try {
            if (! isProduction() && ! isStaging()) {
                throw new Exception('Backup can only be created in production or staging environments.');
            }

            $this->backup_library->create_backup();
            echo "Backup created successfully.";
        } catch (Throwable $e) {
            echo "Error creating backup: " . $e->getMessage();
        }
    }
}