<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller
{
    public function index() {
        if (! is_cli()) show_404();

        $this->load->library('migration');

        if ($this->migration->current() === FALSE) {
            echo $this->migration->error_string();
        } else {
            echo "Migrations ran successfully.\n";
        }
    }
}
