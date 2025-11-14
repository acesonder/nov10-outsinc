<?php
$pageTitle = 'Dashboard - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

Auth::requireLogin();

$db = getDB();
$conn = $db->getConnection();
$userId = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Get role-specific data
$stats = [];

if ($role === 'recipient') {
    // Recipient stats
    $stats['open_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases WHERE recipient_id = $userId AND status = 'open'")->fetch_assoc()['count'];
    $stats['total_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases WHERE recipient_id = $userId")->fetch_assoc()['count'];
    $stats['favorites'] = $conn->query("SELECT COUNT(*) as count FROM favorites WHERE user_id = $userId")->fetch_assoc()['count'];
    $stats['attended_events'] = $conn->query("SELECT COUNT(*) as count FROM event_rsvps WHERE user_id = $userId AND status = 'attending'")->fetch_assoc()['count'];
    
    // Get recent cases
    $cases = $conn->query("SELECT * FROM cases WHERE recipient_id = $userId ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    
} elseif ($role === 'volunteer') {
    // Volunteer stats
    $stats['assigned_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases WHERE assigned_to = $userId AND status IN ('open', 'in_progress')")->fetch_assoc()['count'];
    $stats['completed_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases WHERE assigned_to = $userId AND status = 'closed'")->fetch_assoc()['count'];
    $stats['upcoming_events'] = $conn->query("SELECT COUNT(*) as count FROM event_rsvps WHERE user_id = $userId AND status = 'attending'")->fetch_assoc()['count'];
    $stats['badges_earned'] = getUserBadgeCount($userId);
    
    // Get assigned cases
    $cases = $conn->query("
        SELECT c.*, u.first_name, u.last_name 
        FROM cases c 
        LEFT JOIN users u ON c.recipient_id = u.id 
        WHERE c.assigned_to = $userId AND c.status IN ('open', 'in_progress')
        ORDER BY c.priority DESC, c.created_at DESC 
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);
    
} elseif ($role === 'staff' || $role === 'admin') {
    // Staff/Admin stats
    $stats['total_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases")->fetch_assoc()['count'];
    $stats['open_cases'] = $conn->query("SELECT COUNT(*) as count FROM cases WHERE status = 'open'")->fetch_assoc()['count'];
    $stats['total_users'] = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1")->fetch_assoc()['count'];
    $stats['total_resources'] = $conn->query("SELECT COUNT(*) as count FROM resources WHERE is_active = 1")->fetch_assoc()['count'];
    
    // Get recent cases
    $cases = $conn->query("
        SELECT c.*, u.first_name, u.last_name 
        FROM cases c 
        LEFT JOIN users u ON c.recipient_id = u.id 
        ORDER BY c.created_at DESC 
        LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);
}

// Get user badges
$badges = getUserBadges($userId);

// Get upcoming events
$events = $conn->query("
    SELECT e.* 
    FROM events e
    LEFT JOIN event_rsvps r ON e.id = r.event_id AND r.user_id = $userId
    WHERE e.start_date > NOW() 
    AND (e.is_public = 1 OR r.user_id IS NOT NULL)
    ORDER BY e.start_date ASC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-3">
        <div class="card-header">
            <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($currentUser['first_name']); ?>!</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid mb-4">
        <?php if ($role === 'recipient'): ?>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
                <div class="stat-number"><?php echo $stats['open_cases']; ?></div>
                <div class="stat-label">Open Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-number"><?php echo $stats['total_cases']; ?></div>
                <div class="stat-label">Total Requests</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-heart"></i></div>
                <div class="stat-number"><?php echo $stats['favorites']; ?></div>
                <div class="stat-label">Saved Resources</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                <div class="stat-number"><?php echo $stats['attended_events']; ?></div>
                <div class="stat-label">Events Attended</div>
            </div>
        <?php elseif ($role === 'volunteer'): ?>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-number"><?php echo $stats['assigned_cases']; ?></div>
                <div class="stat-label">Active Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-number"><?php echo $stats['completed_cases']; ?></div>
                <div class="stat-label">Completed Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-calendar"></i></div>
                <div class="stat-number"><?php echo $stats['upcoming_events']; ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-award"></i></div>
                <div class="stat-number"><?php echo $stats['badges_earned']; ?></div>
                <div class="stat-label">Badges Earned</div>
            </div>
        <?php else: ?>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-number"><?php echo $stats['total_cases']; ?></div>
                <div class="stat-label">Total Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-folder-open"></i></div>
                <div class="stat-number"><?php echo $stats['open_cases']; ?></div>
                <div class="stat-label">Open Cases</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-number"><?php echo $stats['total_resources']; ?></div>
                <div class="stat-label">Resources</div>
            </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-2">
        <!-- Cases/Requests -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <?php if ($role === 'recipient'): ?>
                        <i class="fas fa-hand-holding-heart"></i> My Requests
                    <?php elseif ($role === 'volunteer'): ?>
                        <i class="fas fa-tasks"></i> Assigned Cases
                    <?php else: ?>
                        <i class="fas fa-clipboard-list"></i> Recent Cases
                    <?php endif; ?>
                </h3>
                <?php if ($role === 'recipient'): ?>
                    <button onclick="openModal('requestHelpModal')" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Request
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (count($cases) > 0): ?>
                    <?php foreach ($cases as $case): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid var(--light-color); cursor: pointer;" onclick="window.location.href='/public/case.php?id=<?php echo $case['id']; ?>'">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div style="flex: 1;">
                                    <h4><?php echo htmlspecialchars($case['title']); ?></h4>
                                    <?php if (isset($case['first_name'])): ?>
                                        <p class="text-muted" style="font-size: 0.9rem;">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($case['first_name'] . ' ' . $case['last_name']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <p style="font-size: 0.9rem;"><?php echo htmlspecialchars(substr($case['description'], 0, 100)) . '...'; ?></p>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: end;">
                                    <span class="badge <?php echo getCaseStatusBadgeClass($case['status']); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $case['status'])); ?>
                                    </span>
                                    <span class="badge <?php echo getPriorityBadgeClass($case['priority']); ?>">
                                        <?php echo ucfirst($case['priority']); ?>
                                    </span>
                                </div>
                            </div>
                            <p class="text-muted" style="font-size: 0.85rem; margin-top: 0.5rem;">
                                <i class="fas fa-clock"></i> <?php echo timeAgo($case['created_at']); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No cases to display</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar"></i> Upcoming Events</h3>
                <a href="/public/events.php" class="btn btn-primary">
                    <i class="fas fa-eye"></i> View All
                </a>
            </div>
            <div class="card-body">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <div style="padding: 1rem; border-bottom: 1px solid var(--light-color);">
                            <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                            <p class="text-muted" style="font-size: 0.9rem;">
                                <i class="fas fa-calendar"></i> <?php echo formatDateTime($event['start_date']); ?>
                            </p>
                            <p class="text-muted" style="font-size: 0.9rem;">
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            <a href="/public/event.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm mt-2">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-muted">No upcoming events</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Badges Section -->
    <?php if (count($badges) > 0): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3><i class="fas fa-award"></i> My Badges</h3>
            </div>
            <div class="card-body">
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <?php foreach ($badges as $badge): ?>
                        <div style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; padding: 1.5rem; border-radius: 12px; text-align: center; min-width: 150px;">
                            <i class="fas fa-<?php echo htmlspecialchars($badge['icon']); ?>" style="font-size: 2.5rem; margin-bottom: 0.5rem;"></i>
                            <h4><?php echo htmlspecialchars($badge['name']); ?></h4>
                            <p style="font-size: 0.85rem; opacity: 0.9;"><?php echo htmlspecialchars($badge['description']); ?></p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;"><i class="fas fa-star"></i> <?php echo $badge['points']; ?> pts</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="card mt-4 mb-4">
        <div class="card-header">
            <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
        </div>
        <div class="card-body">
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="/public/directory.php" class="btn btn-primary">
                    <i class="fas fa-map-marked-alt"></i> Browse Resources
                </a>
                <a href="/public/map.php" class="btn btn-secondary">
                    <i class="fas fa-map"></i> View Map
                </a>
                <a href="/public/messages.php" class="btn btn-info">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="/public/profile.php" class="btn btn-outline">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <?php if ($role === 'admin'): ?>
                    <a href="/admin/index.php" class="btn btn-danger">
                        <i class="fas fa-shield-alt"></i> Admin Panel
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
