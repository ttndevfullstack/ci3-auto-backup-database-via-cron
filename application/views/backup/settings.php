<!DOCTYPE html>
<html>
<head>
    <title>Backup Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">Backup Settings</h3>
            </div>
            <div class="card-body">
                <?= form_open('backup/settings') ?>
                    <div class="mb-3">
                        <label class="form-label">Backup Time</label>
                        <input type="time" name="backup_time" class="form-control" 
                            value="<?= isset($settings->backup_time) ? $settings->backup_time : '00:00' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Local Backups</label>
                        <input type="number" name="max_local_backups" class="form-control" 
                            value="<?= isset($settings->max_local_backups) ? $settings->max_local_backups : 5 ?>" 
                            min="1" max="100" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="enabled" value="0">
                            <input type="checkbox" name="enabled" class="form-check-input" value="1" 
                                <?= (isset($settings->enabled) && $settings->enabled) ? 'checked' : '' ?>>
                            <label class="form-check-label">Enable Automatic Backups</label>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= site_url('backup') ?>" class="btn btn-secondary">Back</a>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</body>
</html>
