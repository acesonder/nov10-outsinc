// OUTSINC Main JavaScript

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('show');
        document.body.style.overflow = 'auto';
    }
});

// Dropdown functionality
document.addEventListener('click', (e) => {
    const dropdownBtn = e.target.closest('[data-dropdown]');
    
    if (dropdownBtn) {
        e.stopPropagation();
        const dropdownId = dropdownBtn.getAttribute('data-dropdown');
        const dropdown = document.getElementById(dropdownId);
        
        // Close other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu.id !== dropdownId) {
                menu.classList.remove('show');
            }
        });
        
        // Toggle current dropdown
        dropdown.classList.toggle('show');
    } else {
        // Close all dropdowns when clicking outside
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    }
});

// Mobile menu toggle
function toggleMobileMenu() {
    const menu = document.querySelector('.navbar-menu');
    menu.classList.toggle('show');
}

// Show notification toast
function showNotification(message, type = 'info', duration = 3000) {
    const toast = document.getElementById('notificationToast');
    
    // Set color based on type
    const colors = {
        success: '#2ecc71',
        error: '#e74c3c',
        warning: '#f39c12',
        info: '#3498db'
    };
    
    toast.style.background = colors[type] || colors.info;
    toast.style.color = '#fff';
    toast.textContent = message;
    toast.classList.add('show');
    
    // Play notification sound
    const sound = document.getElementById('notificationSound');
    if (sound) {
        sound.play().catch(() => {});
    }
    
    // Auto hide
    setTimeout(() => {
        toast.classList.remove('show');
    }, duration);
}

// Handle login form
async function handleLogin(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/auth.php?action=login', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.href = '/public/dashboard.php';
            }, 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Handle register form
async function handleRegister(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    // Check password match
    const password = formData.get('password');
    const confirmPassword = formData.get('confirm_password');
    
    if (password !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }
    
    try {
        const response = await fetch('/api/auth.php?action=register', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('registerModal');
            setTimeout(() => {
                openModal('loginModal');
            }, 500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Handle password reset
async function handleResetPassword(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/auth.php?action=reset_password', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            closeModal('resetPasswordModal');
            setTimeout(() => {
                openModal('loginModal');
            }, 500);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Handle request help form
async function handleRequestHelp(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/cases.php?action=create', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
            closeModal('requestHelpModal');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Load notifications
async function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    if (!notificationList) return;
    
    try {
        const response = await fetch('/api/notifications.php?action=list&limit=5');
        const data = await response.json();
        
        if (data.success && data.notifications.length > 0) {
            notificationList.innerHTML = data.notifications.map(notif => `
                <div class="dropdown-item ${notif.is_read ? '' : 'unread'}">
                    <div class="notification-content">
                        <strong>${notif.title}</strong>
                        <p>${notif.message}</p>
                        <small>${notif.time_ago}</small>
                    </div>
                </div>
            `).join('');
        } else {
            notificationList.innerHTML = '<p class="text-center text-muted">No notifications</p>';
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// Load messages
async function loadMessages() {
    const messageList = document.getElementById('messageList');
    if (!messageList) return;
    
    try {
        const response = await fetch('/api/messages.php?action=list&limit=5');
        const data = await response.json();
        
        if (data.success && data.messages.length > 0) {
            messageList.innerHTML = data.messages.map(msg => `
                <div class="dropdown-item ${msg.is_read ? '' : 'unread'}">
                    <div class="message-content">
                        <strong>${msg.sender_name}</strong>
                        <p>${msg.message.substring(0, 50)}...</p>
                        <small>${msg.time_ago}</small>
                    </div>
                </div>
            `).join('');
        } else {
            messageList.innerHTML = '<p class="text-center text-muted">No messages</p>';
        }
    } catch (error) {
        console.error('Error loading messages:', error);
    }
}

// Poll for new notifications and messages
function startPolling() {
    // Only poll if user is logged in
    if (document.getElementById('notificationList')) {
        loadNotifications();
        loadMessages();
        
        // Poll every 30 seconds
        setInterval(() => {
            loadNotifications();
            loadMessages();
        }, 30000);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    startPolling();
    
    // Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
});

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 1) return 'just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    
    return date.toLocaleDateString();
}

// AJAX helper
async function ajax(url, method = 'GET', data = null) {
    const options = {
        method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data) {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('AJAX error:', error);
        throw error;
    }
}
