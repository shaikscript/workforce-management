<?php
/**
 * Admin Hub Dashboard
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

// Check admin access
checkAccess('super_admin', 'admin');

// Get company settings
$companySettings = getCompanySettings();

// Fetch KPI data
$stmt = $db->query("SELECT COUNT(*) as total FROM employees WHERE employment_status = 'active'");
$activeEmployees = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM attendance_records WHERE date = CURDATE() AND status = 'present'");
$presentToday = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM candidates WHERE status = 'active'");
$activeCandidates = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM feedback_tickets WHERE status = 'open'");
$openTickets = $stmt->fetch()['total'];

// Fetch monthly payroll trend (simplified)
$stmt = $db->query("SELECT month, year, total_payout FROM payroll_cycles ORDER BY year DESC, month DESC LIMIT 6");
$payrollTrend = $stmt->fetchAll();

// Fetch category scores (simplified)
$stmt = $db->query("SELECT c.name, AVG(e.score_percent) as avg_score FROM employee_assessments e JOIN evaluation_categories c ON e.category_id = c.id GROUP BY c.name");
$categoryScores = $stmt->fetchAll();

?>
<div class="container-fluid">
    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card kpi-card p-3 text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1 opacity-75">Active Employees</h6>
                        <h2 class="kpi-value mb-0"><?php echo $activeEmployees; ?></h2>
                    </div>
                    <i class="bi bi-people fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card p-3 text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1 opacity-75">Present Today</h6>
                        <h2 class="kpi-value mb-0"><?php echo $presentToday; ?></h2>
                    </div>
                    <i class="bi bi-calendar-check fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card p-3 text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1 opacity-75">Active Candidates</h6>
                        <h2 class="kpi-value mb-0"><?php echo $activeCandidates; ?></h2>
                    </div>
                    <i class="bi bi-person-badge fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card p-3 text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="mb-1 opacity-75">Open Tickets</h6>
                        <h2 class="kpi-value mb-0"><?php echo $openTickets; ?></h2>
                    </div>
                    <i class="bi bi-ticket-perforated fs-4 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4">
        <!-- Payroll Trend -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Monthly Payroll Trend</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="payrollChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Category Scores -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-0">
                    <h5 class="card-title mb-0">Category Scores</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mt-4">
        <div class="col-md-3">
            <a href="employees.php" class="btn btn-primary w-100 py-3">
                <i class="bi bi-people me-2"></i> Employee Management
            </a>
        </div>
        <div class="col-md-3">
            <a href="payroll.php" class="btn btn-success w-100 py-3">
                <i class="bi bi-currency-rupee me-2"></i> Payroll Engine
            </a>
        </div>
        <div class="col-md-3">
            <a href="assessments.php" class="btn btn-info w-100 py-3 text-dark">
                <i class="bi bi-graph-up-arrow me-2"></i> AI Evaluations
            </a>
        </div>
        <div class="col-md-3">
            <a href="recruitment.php" class="btn btn-warning w-100 py-3 text-dark">
                <i class="bi bi-person-plus me-2"></i> Recruitment
            </a>
        </div>
    </div>
</div>

<!-- Chart.js Initialization -->
<script>
    // Payroll Trend Chart
    const payrollCtx = document.getElementById('payrollChart').getContext('2d');
    new Chart(payrollCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column(array_reverse($payrollTrend), 'month')); ?>,
            datasets: [{
                label: 'Total Payout (INR)',
                data: <?php echo json_encode(array_column(array_reverse($payrollTrend), 'total_payout')); ?>,
                borderColor: '#2563EB',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(37, 99, 235, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Category Scores Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($categoryScores, 'name')); ?>,
            datasets: [{
                label: 'Average Score (%)',
                data: <?php echo json_encode(array_column($categoryScores, 'avg_score')); ?>,
                backgroundColor: '#10B981',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
