<?php
$pageTitle = 'Messages - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

Auth::requireLogin();

$db = getDB();
$conn = $db->getConnection();
$userId = $_SESSION['user_id'];

// Get all conversations
$conversations = $conn->query("
    SELECT DISTINCT 
        CASE 
            WHEN m.sender_id = $userId THEN m.recipient_id
            ELSE m.sender_id
        END as other_user_id,
        u.first_name, u.last_name, u.profile_image,
        (SELECT message FROM messages 
         WHERE (sender_id = $userId AND recipient_id = other_user_id) 
            OR (sender_id = other_user_id AND recipient_id = $userId)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages 
         WHERE (sender_id = $userId AND recipient_id = other_user_id) 
            OR (sender_id = other_user_id AND recipient_id = $userId)
         ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages 
         WHERE sender_id = other_user_id AND recipient_id = $userId AND is_read = 0) as unread_count
    FROM messages m
    JOIN users u ON (CASE WHEN m.sender_id = $userId THEN m.recipient_id ELSE m.sender_id END) = u.id
    WHERE m.sender_id = $userId OR m.recipient_id = $userId
    ORDER BY last_message_time DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-comments"></i> Messages</h1>
            <button onclick="openModal('newMessageModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Message
            </button>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Conversations List -->
        <div class="card">
            <div class="card-header">
                <h3>Conversations</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <?php if (count($conversations) > 0): ?>
                    <?php foreach ($conversations as $conv): ?>
                        <div class="conversation-item <?php echo $conv['unread_count'] > 0 ? 'unread' : ''; ?>" 
                             onclick="openConversation(<?php echo $conv['other_user_id']; ?>)"
                             style="padding: 1rem; border-bottom: 1px solid var(--light-color); cursor: pointer; transition: var(--transition);"
                             onmouseover="this.style.background='var(--light-color)'"
                             onmouseout="this.style.background='transparent'">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="<?php echo $conv['profile_image'] ?: getAvatarUrl($conv['first_name'] . ' ' . $conv['last_name'], 50); ?>" 
                                     alt="Profile" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="margin: 0; display: flex; justify-content: space-between; align-items: center;">
                                        <?php echo htmlspecialchars($conv['first_name'] . ' ' . $conv['last_name']); ?>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="badge badge-danger"><?php echo $conv['unread_count']; ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.9rem; color: #7f8c8d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?>...
                                    </p>
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem; color: #95a5a6;">
                                        <?php echo timeAgo($conv['last_message_time']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center;">
                        <i class="fas fa-comments" style="font-size: 4rem; color: var(--light-color); margin-bottom: 1rem;"></i>
                        <h3>No messages yet</h3>
                        <p class="text-muted">Start a conversation to get help or offer support</p>
                        <button onclick="openModal('newMessageModal')" class="btn btn-primary mt-2">
                            <i class="fas fa-plus"></i> New Message
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Message Thread -->
        <div class="card">
            <div class="card-header">
                <h3 id="conversationTitle">Select a conversation</h3>
            </div>
            <div class="card-body" id="messageThread" style="max-height: 500px; overflow-y: auto;">
                <div style="text-align: center; padding: 3rem;">
                    <i class="fas fa-arrow-left" style="font-size: 3rem; color: var(--light-color);"></i>
                    <p class="text-muted mt-2">Select a conversation from the list</p>
                </div>
            </div>
            <div class="card-body" id="messageInput" style="display: none; border-top: 2px solid var(--light-color);">
                <form onsubmit="sendMessage(event)" style="display: flex; gap: 0.5rem;">
                    <input type="hidden" id="recipientId">
                    <input type="text" id="messageText" placeholder="Type your message..." style="flex: 1;" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- New Message Modal -->
<div id="newMessageModal" class="modal">
    <div class="modal-content modal-3d">
        <span class="modal-close" onclick="closeModal('newMessageModal')">&times;</span>
        <h2><i class="fas fa-plus"></i> New Message</h2>
        <form id="newMessageForm" onsubmit="handleNewMessage(event)">
            <div class="form-group">
                <label for="newMessageRecipient">To:</label>
                <select id="newMessageRecipient" name="recipient_id" required>
                    <option value="">Select recipient...</option>
                    <?php
                    // Get all users except current user
                    $users = $conn->query("SELECT id, first_name, last_name, role FROM users WHERE id != $userId AND is_active = 1 ORDER BY first_name, last_name")->fetch_all(MYSQLI_ASSOC);
                    foreach ($users as $user) {
                        echo '<option value="' . $user['id'] . '">' . 
                             htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . 
                             ' (' . ucfirst($user['role']) . ')' .
                             '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="newMessageText">Message:</label>
                <textarea id="newMessageText" name="message" rows="5" required></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentConversationId = null;

async function openConversation(userId) {
    currentConversationId = userId;
    document.getElementById('recipientId').value = userId;
    document.getElementById('messageInput').style.display = 'block';
    
    // Load messages
    try {
        const response = await fetch(`/api/messages.php?action=conversation&user_id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            const thread = document.getElementById('messageThread');
            const title = document.getElementById('conversationTitle');
            
            title.textContent = data.recipient_name;
            
            if (data.messages.length > 0) {
                thread.innerHTML = data.messages.map(msg => `
                    <div style="margin-bottom: 1rem; display: flex; justify-content: ${msg.is_mine ? 'flex-end' : 'flex-start'};">
                        <div style="max-width: 70%; padding: 0.75rem 1rem; border-radius: 12px; background: ${msg.is_mine ? 'var(--primary-color)' : 'var(--light-color)'}; color: ${msg.is_mine ? 'white' : 'inherit'};">
                            <p style="margin: 0;">${msg.message}</p>
                            <small style="opacity: 0.8; font-size: 0.75rem;">${msg.time_ago}</small>
                        </div>
                    </div>
                `).join('');
                
                // Scroll to bottom
                thread.scrollTop = thread.scrollHeight;
            } else {
                thread.innerHTML = '<p class="text-center text-muted">No messages yet</p>';
            }
        }
    } catch (error) {
        console.error('Error loading conversation:', error);
    }
}

async function sendMessage(event) {
    event.preventDefault();
    
    const recipientId = document.getElementById('recipientId').value;
    const messageText = document.getElementById('messageText').value;
    
    if (!recipientId || !messageText) return;
    
    try {
        const formData = new FormData();
        formData.append('recipient_id', recipientId);
        formData.append('message', messageText);
        
        const response = await fetch('/api/messages.php?action=send', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('messageText').value = '';
            openConversation(recipientId);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
    }
}

async function handleNewMessage(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/messages.php?action=send', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
            closeModal('newMessageModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
