<?php

class Backup extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('backup_library');
    }

    public function execute(): void
    {
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