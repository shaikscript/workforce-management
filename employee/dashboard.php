<?php
/**
 * Employee Hub Dashboard
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

// Check employee access
checkAccess(null, 'employee');

$employeeId = $_SESSION[USER_SESSION_KEY]['id'];
$employeeName = $_SESSION[USER_SESSION_KEY]['name'];

// Fetch employee data
$stmt = $db->query("SELECT * FROM employees WHERE id = ?", [$employeeId]);
$employee = $stmt->fetch();

// Fetch today's attendance status
$stmt = $db->query("SELECT status, check_in_time, check_out_time FROM attendance_records WHERE employee_id = ? AND date = CURDATE() LIMIT 1", [$employeeId]);
$todayAttendance = $stmt->fetch();

// Fetch latest month net pay (simplified)
$stmt = $db->query("SELECT snapshot_json->>'$.net_payable' as net_pay FROM payslips WHERE employee_id = ? AND status = 'published' ORDER BY generated_at DESC LIMIT 1", [$employeeId]);
$latestPayslip = $stmt->fetch();
$latestNetPay = $latestPayslip['net_pay'] ?? '0.00';

// Fetch overall evaluation score (simplified)
$stmt = $db->query("SELECT AVG(score_percent) as avg_score FROM employee_assessments WHERE employee_id = ? AND published = 1", [$employeeId]);
$avgScore = $stmt->fetch()['avg_score'] ?? '0.00';

// Fetch unread announcements
$stmt = $db->query("SELECT * FROM notifications WHERE recipient_id = ? AND portal = 'employee' AND is_read = 0 ORDER BY created_at DESC LIMIT 5", [$employeeId]);
$unreadNotifications = $stmt->fetchAll();

// Fetch pending leave status
$stmt = $db->query("SELECT * FROM leave_requests WHERE employee_id = ? AND status = 'pending' ORDER BY created_at DESC LIMIT 3", [$employeeId]);
$pendingLeaves = $stmt->fetchAll();

?>
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Welcome back, <?php echo htmlspecialchars($employeeName, ENT_QUOTES, 'UTF-8'); ?>!</h4>
            <p class="text-muted mb-0">Employee ID: <?php echo htmlspecialchars($employee['employee_id'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="theme-toggle" title="Toggle Theme">
                <i class="bi bi-circle-half"></i>
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Check-in/out Status -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Attendance Status</h6>
                    <?php if ($todayAttendance && $todayAttendance['status'] === 'present'): ?>
                        <div class="badge bg-success rounded-pill mb-2">Present</div>
                        <p class="mb-1">Check-in: <?php echo formatTime($todayAttendance['check_in_time']); ?></p>
                        <?php if ($todayAttendance['check_out_time']): ?>
                            <p class="mb-0">Check-out: <?php echo formatTime($todayAttendance['check_out_time']); ?></p>
                        <?php else: ?>
                            <button class="btn btn-sm btn-danger mt-2" onclick="checkOut()">Check Out</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="badge bg-warning text-dark rounded-pill mb-2">Not Checked In</div>
                        <button class="btn btn-sm btn-success" onclick="checkIn()">Check In</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Latest Net Pay -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Latest Net Pay</h6>
                    <h2 class="text-primary mb-0">₹<?php echo number_format($latestNetPay, 2); ?></h2>
                    <p class="text-muted small mb-0">Last Month</p>
                </div>
            </div>
        </div>
        
        <!-- Evaluation Score -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Overall Score</h6>
                    <h2 class="text-success mb-0"><?php echo number_format($avgScore, 2); ?>%</h2>
                    <?php echo getRatingBadge($avgScore); ?>
                </div>
            </div>
        </div>
        
        <!-- Unread Notifications -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-3">Unread Notifications</h6>
                    <h2 class="text-info mb-0"><?php echo count($unreadNotifications); ?></h2>
                    <a href="notifications.php" class="btn btn-sm btn-outline-info mt-2">View All</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-calendar3 me-2"></i> Attendance & Leaves</h5>
                    <p class="text-muted">Manage your attendance and apply for leaves.</p>
                    <div class="d-flex gap-2">
                        <a href="attendance.php" class="btn btn-sm btn-outline-primary">View Calendar</a>
                        <a href="leave_requests.php" class="btn btn-sm btn-outline-success">Apply Leave</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-currency-rupee me-2"></i> Payroll & Payslips</h5>
                    <p class="text-muted">View salary structure and download payslips.</p>
                    <a href="payslips.php" class="btn btn-sm btn-outline-primary">View Payslips</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-graph-up-arrow me-2"></i> AI Evaluations</h5>
                    <p class="text-muted">Track your performance and category scores.</p>
                    <a href="evaluations.php" class="btn btn-sm btn-outline-primary">View Scores</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leaves & Notifications -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Pending Leave Requests</h5>
                </div>
                <div class="card-body">
                    <?php if (count($pendingLeaves) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($pendingLeaves as $leave): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block"><?php echo formatDate($leave['start_date']); ?> - <?php echo formatDate($leave['end_date']); ?></strong>
                                        <small class="text-muted"><?php echo $leave['leave_type']; ?></small>
                                    </div>
                                    <?php echo getStatusBadge($leave['status'], 'pill'); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-check fs-3"></i>
                            <p class="mt-2">No pending leave requests</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Recent Notifications</h5>
                </div>
                <div class="card-body">
                    <?php if (count($unreadNotifications) > 0): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($unreadNotifications as $notification): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong class="d-block"><?php echo htmlspecialchars($notification['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <small class="text-muted"><?php echo formatDate($notification['created_at']); ?></small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-secondary" onclick="markAsRead(<?php echo $notification['id']; ?>)">Mark Read</button>
                                    </div>
                                    <p class="mb-0 mt-1 small text-muted"><?php echo htmlspecialchars(substr($notification['content'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="bi bi-bell fs-3"></i>
                            <p class="mt-2">No unread notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Attendance functions
    function checkIn() {
        showToast('Checking in...', 'info');
        fetch('ajax/attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf': getCsrfToken(),
                'action': 'check_in'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Checked in successfully!', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Error', 'danger');
            }
        });
    }

    function checkOut() {
        showToast('Checking out...', 'info');
        fetch('ajax/attendance.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf': getCsrfToken(),
                'action': 'check_out'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Checked out successfully!', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Error', 'danger');
            }
        });
    }

    // Mark notification as read
    function markAsRead(notificationId) {
        fetch('ajax/notifications.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                '_csrf': getCsrfToken(),
                'action': 'mark_read',
                'id': notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Notification marked as read', 'success');
                location.reload();
            } else {
                showToast(data.message || 'Error', 'danger');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
