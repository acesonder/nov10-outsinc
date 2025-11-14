<?php
$pageTitle = 'Events & Volunteer - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conn = $db->getConnection();

// Get upcoming events
$events = $conn->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM event_rsvps WHERE event_id = e.id AND status = 'attending') as attendees
    FROM events e
    WHERE e.start_date > NOW() AND e.is_public = 1
    ORDER BY e.start_date ASC
")->fetch_all(MYSQLI_ASSOC);

// Get user RSVPs if logged in
$userRsvps = [];
if (Auth::isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $result = $conn->query("SELECT event_id, status FROM event_rsvps WHERE user_id = $userId");
    while ($row = $result->fetch_assoc()) {
        $userRsvps[$row['event_id']] = $row['status'];
    }
}
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-calendar"></i> Events & Volunteer Opportunities</h1>
            <p>Join us in making a difference in our community</p>
        </div>
    </div>

    <?php if (count($events) > 0): ?>
        <div class="grid grid-2">
            <?php foreach ($events as $event): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                        <span class="badge badge-info">
                            <?php echo ucfirst($event['type']); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <p><?php echo htmlspecialchars($event['description']); ?></p>
                        
                        <div style="margin: 1rem 0;">
                            <p><i class="fas fa-calendar"></i> <?php echo formatDateTime($event['start_date']); ?></p>
                            <?php if ($event['end_date'] !== $event['start_date']): ?>
                                <p><i class="fas fa-calendar-check"></i> Ends: <?php echo formatDateTime($event['end_date']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($event['location']): ?>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                            <?php endif; ?>
                            
                            <p><i class="fas fa-users"></i> <?php echo $event['attendees']; ?> attending
                                <?php if ($event['max_participants']): ?>
                                    / <?php echo $event['max_participants']; ?> max
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <?php if (Auth::isLoggedIn()): ?>
                            <?php
                            $userStatus = $userRsvps[$event['id']] ?? null;
                            $isAttending = $userStatus === 'attending';
                            $isFull = $event['max_participants'] && $event['attendees'] >= $event['max_participants'];
                            ?>
                            
                            <?php if ($isAttending): ?>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-check"></i> Attending
                                    </button>
                                    <button class="btn btn-outline" onclick="updateRsvp(<?php echo $event['id']; ?>, 'declined')">
                                        Cancel
                                    </button>
                                </div>
                            <?php elseif ($isFull): ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-users"></i> Event Full
                                </button>
                            <?php else: ?>
                                <button class="btn btn-primary" onclick="updateRsvp(<?php echo $event['id']; ?>, 'attending')">
                                    <i class="fas fa-check-circle"></i> RSVP
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="btn btn-primary" onclick="openModal('loginModal')">
                                <i class="fas fa-sign-in-alt"></i> Login to RSVP
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-calendar" style="font-size: 5rem; color: var(--light-color); margin-bottom: 1rem;"></i>
                <h3>No Upcoming Events</h3>
                <p>Check back soon for new volunteer opportunities!</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Volunteer Information -->
    <div class="card mt-4 mb-4">
        <div class="card-header">
            <h2><i class="fas fa-hands-helping"></i> Why Volunteer?</h2>
        </div>
        <div class="card-body">
            <div class="grid grid-3">
                <div style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-heart" style="font-size: 3rem; color: var(--danger-color); margin-bottom: 1rem;"></i>
                    <h4>Make a Difference</h4>
                    <p>Directly impact the lives of people in need in your community</p>
                </div>
                <div style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-users" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <h4>Build Community</h4>
                    <p>Connect with like-minded individuals who care about helping others</p>
                </div>
                <div style="text-align: center; padding: 1.5rem;">
                    <i class="fas fa-award" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                    <h4>Earn Recognition</h4>
                    <p>Receive badges and achievements for your volunteer contributions</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function updateRsvp(eventId, status) {
    try {
        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('status', status);
        
        const response = await fetch('/api/events.php?action=rsvp', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
