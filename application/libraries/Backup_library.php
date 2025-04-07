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

        $this->create_local_backup($file_name, $backup);

        $this->upload_to_s3($file_name, $backup);

        $this->cleanup_old_backups();
    }

    public function create_local_backups(string $file_name, $backup): void
    {
        try {
            $local_backup_file_path = $this->generate_local_backup_path($file_name);

            if (! file_exists($local_backup_file_path)) {
                mkdir($local_backup_file_path, 0755, true);
            }

            file_put_contents($local_backup_file_path, $backup);
        } catch (Throwable $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
        }
    }

    public function upload_to_s3(string $file_name): void
    {
        try {
            $this->s3->putObject([
                'Bucket' => $this->s3_bucket,
                'Key' => $file_name,
                'SourceFile' => $this->generate_local_backup_path($file_name),
                'ACL' => 'private',
            ]);
        } catch (Throwable $e) {
            log_message('error', 'S3 Upload Error: ' . $e->getMessage());
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

    private function generate_local_backup_path(string $file_name): string
    {
        return $this->backup_path . $file_name;
    }
}
