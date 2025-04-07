<!DOCTYPE html>
<html>
<head>
    <title>Backup Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Database Backup Management</h2>
            <div>
                <a href="<?= site_url('backup/settings') ?>" class="btn btn-primary">Settings</a>
                <a href="<?= site_url('backup/execute') ?>" class="btn btn-success">Create Backup Now</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Backup Status</h5>
            </div>
            <div class="card-body">
                <?php if (isset($settings) && $settings): ?>
                    <p>Next backup scheduled at: <strong><?= $settings->backup_time ?></strong></p>
                    <p>Max backup versions: <strong><?= $settings->max_local_backups ?></strong></p>
                    <p>Status: <strong><?= $settings->enabled ? 'Enabled' : 'Disabled' ?></strong></p>
                <?php else: ?>
                    <p class="text-muted">No backup settings configured. Please configure in settings.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Backup History</h5>
            </div>
            <div class="card-body">
                <?php if (isset($history) && !empty($history)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Filename</th>
                                <th>Size</th>
                                <th>Status</th>
                                <th>S3 URL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $backup): ?>
                            <tr>
                                <td><?= $backup->created_at ?></td>
                                <td><?= $backup->filename ?></td>
                                <td><?= number_format($backup->size / 1024 / 1024, 2) ?> MB</td>
                                <td>
                                    <span class="badge bg-<?= $backup->status === 'completed' ? 'success' : 
                                        ($backup->status === 'failed' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($backup->status) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($backup->s3_url): ?>
                                        <a href="<?= $backup->s3_url ?>" target="_blank">Download</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No backup history available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
