<?php
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/config.php';
}
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

$currentUser = Auth::getCurrentUser();
$isLoggedIn = Auth::isLoggedIn();
$unreadNotifications = $isLoggedIn ? getUnreadNotificationCount($_SESSION['user_id']) : 0;
$unreadMessages = $isLoggedIn ? getUnreadMessageCount($_SESSION['user_id']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'OUTSINC - Outreach Someone In Need of Change'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="/public/css/main.css">
    <?php if (isset($additionalCSS)) echo $additionalCSS; ?>
</head>
<body class="<?php echo $currentUser['theme'] ?? 'light'; ?>-theme">
    <!-- Navigation -->
    <nav class="navbar navbar-3d">
        <div class="container">
            <div class="navbar-brand">
                <a href="/public/index.php" class="logo">
                    <i class="fas fa-hands-helping"></i>
                    <span>OUTSINC</span>
                </a>
            </div>
            
            <div class="navbar-menu">
                <a href="/public/index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="/public/directory.php" class="nav-link">
                    <i class="fas fa-map-marked-alt"></i> Directory
                </a>
                <a href="/public/map.php" class="nav-link">
                    <i class="fas fa-map"></i> Map
                </a>
                <a href="/public/stories.php" class="nav-link">
                    <i class="fas fa-book-open"></i> Stories
                </a>
                <a href="/public/events.php" class="nav-link">
                    <i class="fas fa-calendar"></i> Events
                </a>
                <a href="/public/contact.php" class="nav-link">
                    <i class="fas fa-envelope"></i> Contact
                </a>
                
                <?php if ($isLoggedIn): ?>
                    <a href="/public/dashboard.php" class="nav-link">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <div class="nav-item dropdown">
                        <button class="nav-link notification-btn" data-dropdown="notifications">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadNotifications > 0): ?>
                                <span class="badge"><?php echo $unreadNotifications; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu" id="notifications">
                            <div class="dropdown-header">Notifications</div>
                            <div class="notification-list" id="notificationList">
                                <p class="text-center text-muted">Loading...</p>
                            </div>
                            <a href="/public/notifications.php" class="dropdown-footer">View all</a>
                        </div>
                    </div>
                    
                    <div class="nav-item dropdown">
                        <button class="nav-link notification-btn" data-dropdown="messages">
                            <i class="fas fa-comments"></i>
                            <?php if ($unreadMessages > 0): ?>
                                <span class="badge"><?php echo $unreadMessages; ?></span>
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu" id="messages">
                            <div class="dropdown-header">Messages</div>
                            <div class="message-list" id="messageList">
                                <p class="text-center text-muted">Loading...</p>
                            </div>
                            <a href="/public/messages.php" class="dropdown-footer">View all</a>
                        </div>
                    </div>
                    
                    <div class="nav-item dropdown">
                        <button class="nav-link" data-dropdown="profile">
                            <img src="<?php echo $currentUser['profile_image'] ?: getAvatarUrl($currentUser['first_name'] . ' ' . $currentUser['last_name'], 40); ?>" alt="Profile" class="profile-img">
                            <span><?php echo $currentUser['first_name']; ?></span>
                        </button>
                        <div class="dropdown-menu" id="profile">
                            <a href="/public/profile.php" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="/public/settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="/admin/index.php" class="dropdown-item">
                                    <i class="fas fa-shield-alt"></i> Admin Panel
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a href="/api/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button class="btn btn-primary" onclick="openModal('loginModal')">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                    <button class="btn btn-outline" onclick="openModal('registerModal')">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                <?php endif; ?>
            </div>
            
            <button class="navbar-toggle" onclick="toggleMobileMenu()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <div class="content-wrapper">
