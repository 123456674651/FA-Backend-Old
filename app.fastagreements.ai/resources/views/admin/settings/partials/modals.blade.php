<!-- Import Settings Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('settings.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="importModalLabel"><i class="bi bi-upload"></i> Import Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i> Importing settings from a backup JSON file will overwrite all existing config values in the database.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Settings JSON File</label>
                        <input type="file" name="import_file" class="form-control" accept=".json" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-check-circle"></i> Import Backup</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Test SMTP Connection Modal -->
<div class="modal fade" id="testSmtpModal" tabindex="-1" aria-labelledby="testSmtpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="testSmtpForm" action="{{ route('settings.test-smtp') }}" method="POST">
            @csrf
            <!-- Hidden dynamic fields to hold current input values before testing -->
            <input type="hidden" name="mail_driver">
            <input type="hidden" name="mail_host">
            <input type="hidden" name="mail_port">
            <input type="hidden" name="mail_username">
            <input type="hidden" name="mail_password">
            <input type="hidden" name="mail_encryption">
            <input type="hidden" name="mail_from_name">
            <input type="hidden" name="mail_from_email">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="testSmtpModalLabel"><i class="bi bi-envelope"></i> Send Test SMTP Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> This will test the SMTP parameters currently entered in the SMTP form (without saving them).
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Recipient Email Address</label>
                        <input type="email" name="test_email" class="form-control" placeholder="recipient@example.com" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-send"></i> Send Test</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Test Firebase Notification Modal -->
<div class="modal fade" id="testFirebaseModal" tabindex="-1" aria-labelledby="testFirebaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="testFirebaseForm" action="{{ route('settings.test-firebase') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="testFirebaseModalLabel"><i class="bi bi-bell"></i> Send Firebase Test Push</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle"></i> Sends a push message to a specific device using the Firebase credentials configured in the form.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Device Token (FCM Registration Token)</label>
                        <input type="text" name="test_token" class="form-control" placeholder="Enter device token" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notification Title</label>
                        <input type="text" name="title" class="form-control" value="Fast Agreements Test" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notification Body</label>
                        <input type="text" name="body" class="form-control" value="This is a test push notification from your settings module." required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-send"></i> Send Test Notification</button>
                </div>
            </div>
        </form>
    </div>
</div>
