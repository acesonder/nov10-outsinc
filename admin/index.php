<?php
$pageTitle = 'Admin Panel - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

Auth::requireRole('admin');

$db = getDB();
$conn = $db->getConnection();

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_cases' => $conn->query("SELECT COUNT(*) as count FROM cases")->fetch_assoc()['count'],
    'open_cases' => $conn->query("SELECT COUNT(*) as count FROM cases WHERE status = 'open'")->fetch_assoc()['count'],
    'total_resources' => $conn->query("SELECT COUNT(*) as count FROM resources")->fetch_assoc()['count'],
    'total_events' => $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'],
    'pending_stories' => $conn->query("SELECT COUNT(*) as count FROM success_stories WHERE is_published = 0")->fetch_assoc()['count'],
];

// Get recent activity
$recentActivity = $conn->query("
    SELECT * FROM audit_logs 
    ORDER BY created_at DESC 
    LIMIT 20
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-shield-alt"></i> Admin Panel</h1>
            <p>Manage users, resources, and platform settings</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_cases']); ?></div>
            <div class="stat-label">Total Cases</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
            <div class="stat-number"><?php echo number_format($stats['open_cases']); ?></div>
            <div class="stat-label">Open Cases</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="stat-number"><?php echo number_format($stats['total_resources']); ?></div>
            <div class="stat-label">Resources</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-4">
                <a href="/admin/users.php" class="btn btn-primary btn-block">
                    <i class="fas fa-users"></i><br>Manage Users
                </a>
                <a href="/admin/resources.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-map-marked-alt"></i><br>Manage Resources
                </a>
                <a href="/admin/cases.php" class="btn btn-info btn-block">
                    <i class="fas fa-clipboard-list"></i><br>Manage Cases
                </a>
                <a href="/admin/events.php" class="btn btn-warning btn-block">
                    <i class="fas fa-calendar"></i><br>Manage Events
                </a>
                <a href="/admin/stories.php" class="btn btn-primary btn-block">
                    <i class="fas fa-book"></i><br>Review Stories
                    <?php if ($stats['pending_stories'] > 0): ?>
                        <span class="badge"><?php echo $stats['pending_stories']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="/admin/banners.php" class="btn btn-secondary btn-block">
                    <i class="fas fa-image"></i><br>Manage Banners
                </a>
                <a href="/admin/announcements.php" class="btn btn-info btn-block">
                    <i class="fas fa-bullhorn"></i><br>Announcements
                </a>
                <a href="/admin/analytics.php" class="btn btn-warning btn-block">
                    <i class="fas fa-chart-bar"></i><br>Analytics
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mb-4">
        <div class="card-header">
            <h2><i class="fas fa-history"></i> Recent Activity</h2>
        </div>
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--light-color);">
                            <th style="padding: 1rem; text-align: left;">User</th>
                            <th style="padding: 1rem; text-align: left;">Action</th>
                            <th style="padding: 1rem; text-align: left;">Entity</th>
                            <th style="padding: 1rem; text-align: left;">Time</th>
                            <th style="padding: 1rem; text-align: left;">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivity as $activity): ?>
                            <tr style="border-bottom: 1px solid var(--light-color);">
                                <td style="padding: 1rem;">
                                    <?php 
                                    if ($activity['user_id']) {
                                        $user = $conn->query("SELECT first_name, last_name FROM users WHERE id = " . $activity['user_id'])->fetch_assoc();
                                        echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                                    } else {
                                        echo 'Guest';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($activity['action']); ?></td>
                                <td style="padding: 1rem;">
                                    <?php 
                                    if ($activity['entity_type']) {
                                        echo htmlspecialchars($activity['entity_type'] . ' #' . $activity['entity_id']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td style="padding: 1rem;"><?php echo timeAgo($activity['created_at']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
