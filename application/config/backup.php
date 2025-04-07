<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['backup_path'] = APPPATH . 'backups/';
$config['s3_bucket'] = 'your-backup-bucket';
$config['s3_region'] = 'us-east-1';
$config['max_local_backups'] = 5; // Keep last 5 backups locally