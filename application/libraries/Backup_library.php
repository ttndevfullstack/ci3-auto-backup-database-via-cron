<?php

use Aws\S3\S3Client;

defined('BASEPATH') or exit('No direct script access allowed');

class Backup_library
{
    protected $CI;

    protected $s3;

    protected string $backup_path = '';

    protected int $max_local_backups;

    protected string $s3_bucket;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model('backup_model');
        $this->CI->load->database();

        $this->CI->config->load('aws');
        $this->CI->config->load('backup');

        $this->backup_path = $this->CI->config->item('backup_path');
        $this->max_local_backups = $this->CI->config->item('max_local_backups');
        $this->s3_bucket = $this->CI->config->item('s3_bucket');

        if (! $this->backup_path || ! $this->max_local_backups || ! $this->s3_bucket) {
            throw new Exception('Backup path or max local backups not set in config.');
        }

        if (! $this->CI->config->item('aws_access_key') || ! $this->CI->config->item('aws_secret_key')) {
            throw new Exception('AWS credentials not set in config.');
        }

        $this->s3 = new S3Client([
            'version' => 'latest',
            'region' => $this->CI->config->item('s3_region'),
            'credentials' => [
                'key' => $this->CI->config->item('aws_access_key'),
                'secret' => $this->CI->config->item('aws_secret_key'),
            ]
        ]);
    }

    public function create_backup(): void
    {
        $file_name = 'backup_' . date('Ymd_His') . '.sql';

        $this->CI->load->dbutil();
        $backup = $this->CI->dbutil->backup();

        $backup_id = $this->CI->backup_model->create_backup_history($file_name, strlen($backup));

        try {
            $this->create_local_backup($file_name, $backup);
            $s3_url = $this->upload_to_s3($file_name);

            $this->CI->backup_model->update_backup_status($backup_id, 'completed', $s3_url);
            $this->cleanup_old_backups();
        } catch (Throwable $e) {
            $this->CI->backup_model->update_backup_status($backup_id, 'failed');
            throw $e;
        }
    }

    public function create_local_backup(string $file_name, $backup): void
    {
        try {
            $local_backup_file_path = $this->generate_local_backup_path($file_name);

            if (! file_exists($local_backup_file_path)) {
                if (! file_exists($this->backup_path)) {
                    mkdir($this->backup_path, 0755, true);
                }

                file_put_contents($local_backup_file_path, '');
            }

            file_put_contents($local_backup_file_path, $backup);
        } catch (Throwable $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
        }
    }

    public function upload_to_s3(string $file_name): string
    {
        try {
            $result = $this->s3->putObject([
                'Bucket' => $this->s3_bucket,
                'Key' => $file_name,
                'SourceFile' => $this->generate_local_backup_path($file_name),
                'ACL' => 'private',
            ]);
            return $result['ObjectURL'];
        } catch (Throwable $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function cleanup_old_backups(): void
    {
        $files = scandir($this->backup_path);
        $sorted_files = array_diff($files, ['.', '..']);
        rsort($sorted_files);

        foreach ($sorted_files as $file) {
            if (is_file($this->backup_path . $file) && $file != $this->CI->config->item('s3_bucket')) {
                unlink($this->backup_path . $file);
            }

            if (count(glob("$this->backup_path*")) > $this->max_local_backups) {
                break;
            }
        }
    }

    public function get_backup_settings()
    {
        return $this->CI->backup_model->get_settings();
    }

    public function update_backup_settings($data)
    {
        $this->CI->backup_model->update_settings($data);
        $this->update_cron_schedule($data['backup_time']);
    }

    private function update_cron_schedule($time)
    {
        // Update system crontab
        $cron_time = explode(':', $time);
        $cron_command = "{$cron_time[1]} {$cron_time[0]} * * * php " . FCPATH . "index.php backup execute";
        exec("(crontab -l | grep -v 'backup execute') | crontab -");
        exec("(crontab -l; echo \"$cron_command\") | crontab -");
    }

    private function generate_local_backup_path(string $file_name): string
    {
        return $this->backup_path . $file_name;
    }
}
