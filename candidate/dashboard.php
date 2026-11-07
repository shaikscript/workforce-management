<?php
/**
 * Candidate Hub Dashboard
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

// Check candidate access
checkAccess(null, 'candidate');

$candidateId = $_SESSION[USER_SESSION_KEY]['id'];
$candidateName = $_SESSION[USER_SESSION_KEY]['name'];

// Fetch assigned tests
$stmt = $db->query("
    SELECT a.id as assignment_id, t.title, t.duration_minutes, t.max_attempts, 
           ca.status as attempt_status, ca.result_published, ca.percentage,
           a.start_deadline, a.expiry_deadline
    FROM candidate_test_assignments a
    JOIN tests t ON a.test_id = t.id
    LEFT JOIN candidate_attempts ca ON a.id = ca.assignment_id AND ca.candidate_id = ?
    WHERE a.candidate_id = ?
    ORDER BY a.expiry_deadline ASC
", [$candidateId, $candidateId]);
$assignedTests = $stmt->fetchAll();

?>
<div class="container-fluid">
    <!-- Welcome Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Welcome, <?php echo htmlspecialchars($candidateName, ENT_QUOTES, 'UTF-8'); ?>!</h4>
            <p class="text-muted mb-0">Candidate ID: <?php echo htmlspecialchars($_SESSION[USER_SESSION_KEY]['id'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="theme-toggle" title="Toggle Theme">
                <i class="bi bi-circle-half"></i>
            </button>
        </div>
    </div>

    <!-- Assigned Assessments -->
    <div class="card">
        <div class="card-header bg-transparent border-0">
            <h5 class="card-title mb-0">Assigned Assessments</h5>
        </div>
        <div class="card-body">
            <?php if (count($assignedTests) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Test Title</th>
                                <th>Duration</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedTests as $test): ?>
                                <tr>
                                    <td>
                                        <strong class="d-block"><?php echo htmlspecialchars($test['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <small class="text-muted"><?php echo $test['max_attempts']; ?> attempt(s) allowed</small>
                                    </td>
                                    <td><?php echo $test['duration_minutes']; ?> min</td>
                                    <td>
                                        <small>
                                            Starts: <?php echo formatDate($test['start_deadline']); ?><br>
                                            Expires: <?php echo formatDate($test['expiry_deadline']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($test['attempt_status'] === 'in_progress') {
                                            echo '<span class="badge bg-warning text-dark">In Progress</span>';
                                        } elseif ($test['attempt_status'] === 'submitted') {
                                            echo '<span class="badge bg-info text-dark">Under Review</span>';
                                        } elseif ($test['result_published'] === 1) {
                                            echo '<span class="badge bg-success">Published</span>';
                                        } else {
                                            echo '<span class="badge bg-secondary">Not Started</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($test['result_published'] === 1): ?>
                                            <span class="badge bg-success"><?php echo number_format($test['percentage'], 2); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($test['attempt_status'] === 'not_started' || $test['attempt_status'] === null): ?>
                                            <a href="start_test.php?assignment=<?php echo $test['assignment_id']; ?>" class="btn btn-sm btn-primary">Start Test</a>
                                        <?php elseif ($test['attempt_status'] === 'in_progress'): ?>
                                            <a href="resume_test.php?assignment=<?php echo $test['assignment_id']; ?>" class="btn btn-sm btn-warning">Resume</a>
                                        <?php elseif ($test['result_published'] === 1): ?>
                                            <a href="view_result.php?assignment=<?php echo $test['assignment_id']; ?>" class="btn btn-sm btn-success">View Result</a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>Waiting</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state py-5">
                    <i class="bi bi-clipboard-check fs-3"></i>
                    <p class="mt-2">No assessments assigned yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
