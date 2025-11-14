<?php
$pageTitle = 'OUTSINC - Outreach Someone In Need of Change';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conn = $db->getConnection();

// Get stats
$peopleHelped = getPeopleHelpedCount();
$totalResources = $conn->query("SELECT COUNT(*) as count FROM resources WHERE is_active = 1")->fetch_assoc()['count'];
$activeVolunteers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'volunteer' AND is_active = 1")->fetch_assoc()['count'];
$upcomingEvents = $conn->query("SELECT COUNT(*) as count FROM events WHERE start_date > NOW() AND is_public = 1")->fetch_assoc()['count'];

// Get active banners
$banners = $conn->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY display_order LIMIT 1")->fetch_all(MYSQLI_ASSOC);

// Get recent success stories
$stories = $conn->query("SELECT * FROM success_stories WHERE is_published = 1 ORDER BY published_at DESC LIMIT 3")->fetch_all(MYSQLI_ASSOC);

// Get active announcements
$announcements = $conn->query("SELECT * FROM announcements WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY created_at DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <!-- Active Announcements -->
    <?php foreach ($announcements as $announcement): ?>
        <div class="card mb-3" style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white;">
            <div style="padding: 1rem;">
                <h4><i class="fas fa-bullhorn"></i> <?php echo htmlspecialchars($announcement['title']); ?></h4>
                <p><?php echo htmlspecialchars($announcement['content']); ?></p>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Hero Section -->
    <section class="hero">
        <h1><i class="fas fa-hands-helping"></i> Welcome to OUTSINC</h1>
        <p>Outreach Someone In Need of Change</p>
        <p>Connecting those in need with resources, support, volunteers, and the community</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 2rem;">
            <a href="/public/directory.php" class="btn btn-secondary">
                <i class="fas fa-map-marked-alt"></i> Find Resources
            </a>
            <button onclick="openModal('requestHelpModal')" class="btn btn-outline">
                <i class="fas fa-hand-holding-heart"></i> Request Help
            </button>
            <a href="/public/events.php" class="btn btn-outline">
                <i class="fas fa-calendar"></i> Volunteer
            </a>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo number_format($peopleHelped); ?></div>
            <div class="stat-label">People Helped</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
            <div class="stat-number"><?php echo number_format($totalResources); ?></div>
            <div class="stat-label">Resources Available</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-hands-helping"></i></div>
            <div class="stat-number"><?php echo number_format($activeVolunteers); ?></div>
            <div class="stat-label">Active Volunteers</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div class="stat-number"><?php echo number_format($upcomingEvents); ?></div>
            <div class="stat-label">Upcoming Events</div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mt-4">
        <div class="card">
            <h2><i class="fas fa-bullseye"></i> Our Mission</h2>
            <p style="font-size: 1.1rem; line-height: 1.8; margin-top: 1rem;">
                OUTSINC is dedicated to helping people experiencing homelessness, mental health issues, or substance use challenges. 
                Our platform connects those in need with essential resources, compassionate support, dedicated volunteers, and a caring community. 
                We believe everyone deserves a chance at a better life, and we're here to make that possible.
            </p>
        </div>
    </section>

    <!-- Quick Links -->
    <section class="mt-4">
        <h2 class="text-center mb-3"><i class="fas fa-link"></i> Quick Access</h2>
        <div class="grid grid-3">
            <a href="/public/directory.php" class="card" style="text-decoration: none; color: inherit;">
                <div style="text-align: center;">
                    <i class="fas fa-list" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <h3>Service Directory</h3>
                    <p>Browse our comprehensive directory of shelters, food banks, clinics, and support services.</p>
                </div>
            </a>
            <a href="/public/map.php" class="card" style="text-decoration: none; color: inherit;">
                <div style="text-align: center;">
                    <i class="fas fa-map" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 1rem;"></i>
                    <h3>Outreach Map</h3>
                    <p>View an interactive map of all resources and services in your area.</p>
                </div>
            </a>
            <a href="/public/events.php" class="card" style="text-decoration: none; color: inherit;">
                <div style="text-align: center;">
                    <i class="fas fa-calendar-alt" style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;"></i>
                    <h3>Events & Volunteer</h3>
                    <p>Find volunteer opportunities and community events near you.</p>
                </div>
            </a>
        </div>
    </section>

    <!-- Success Stories -->
    <?php if (count($stories) > 0): ?>
        <section class="mt-4">
            <h2 class="text-center mb-3"><i class="fas fa-heart"></i> Success Stories</h2>
            <div class="grid grid-3">
                <?php foreach ($stories as $story): ?>
                    <div class="card">
                        <?php if ($story['media_type'] === 'image' && $story['media_url']): ?>
                            <img src="<?php echo htmlspecialchars($story['media_url']); ?>" alt="Success Story" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 1rem;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 8px; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-book-open" style="font-size: 4rem; color: white;"></i>
                            </div>
                        <?php endif; ?>
                        <h4><?php echo htmlspecialchars($story['title']); ?></h4>
                        <p><?php echo htmlspecialchars(substr($story['story'], 0, 150)) . '...'; ?></p>
                        <a href="/public/story.php?id=<?php echo $story['id']; ?>" class="btn btn-primary mt-2">
                            <i class="fas fa-book-open"></i> Read More
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-3">
                <a href="/public/stories.php" class="btn btn-outline">
                    <i class="fas fa-book"></i> View All Stories
                </a>
            </div>
        </section>
    <?php endif; ?>

    <!-- Call to Action -->
    <section class="mt-4 mb-4">
        <div class="card" style="background: linear-gradient(135deg, var(--secondary-color), #27ae60); color: white; text-align: center; padding: 3rem;">
            <h2><i class="fas fa-hands-helping"></i> Get Involved Today</h2>
            <p style="font-size: 1.2rem; margin: 1.5rem 0;">Whether you need help or want to help others, OUTSINC is here for you.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <?php if (!$isLoggedIn): ?>
                    <button onclick="openModal('registerModal')" class="btn btn-outline" style="background: white; color: var(--secondary-color);">
                        <i class="fas fa-user-plus"></i> Register Now
                    </button>
                <?php else: ?>
                    <a href="/public/dashboard.php" class="btn btn-outline" style="background: white; color: var(--secondary-color);">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                <?php endif; ?>
                <a href="/public/contact.php" class="btn btn-outline">
                    <i class="fas fa-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
