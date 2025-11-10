<?php
$pageTitle = 'Success Stories - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conn = $db->getConnection();

// Get published stories
$stories = $conn->query("
    SELECT s.*, u.first_name, u.last_name 
    FROM success_stories s 
    LEFT JOIN users u ON s.author_id = u.id 
    WHERE s.is_published = 1 
    ORDER BY s.published_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-book-open"></i> Success Stories</h1>
            <p>Read inspiring stories of hope, recovery, and transformation</p>
        </div>
        <?php if (Auth::isLoggedIn()): ?>
            <div class="card-body">
                <button onclick="openModal('submitStoryModal')" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Share Your Story
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if (count($stories) > 0): ?>
        <div class="grid grid-2">
            <?php foreach ($stories as $story): ?>
                <div class="card">
                    <?php if ($story['media_type'] === 'image' && $story['media_url']): ?>
                        <img src="<?php echo htmlspecialchars($story['media_url']); ?>" alt="Story image" 
                             style="width: 100%; height: 250px; object-fit: cover; border-radius: 8px 8px 0 0;">
                    <?php elseif ($story['media_type'] === 'video' && $story['media_url']): ?>
                        <video controls style="width: 100%; height: 250px; border-radius: 8px 8px 0 0;">
                            <source src="<?php echo htmlspecialchars($story['media_url']); ?>">
                        </video>
                    <?php else: ?>
                        <div style="width: 100%; height: 250px; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 8px 8px 0 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book-open" style="font-size: 5rem; color: white;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h3><?php echo htmlspecialchars($story['title']); ?></h3>
                        
                        <p class="text-muted" style="font-size: 0.9rem;">
                            <i class="fas fa-user"></i> 
                            <?php 
                            if ($story['first_name']) {
                                echo 'By ' . htmlspecialchars($story['first_name'] . ' ' . $story['last_name']);
                            } else {
                                echo 'Anonymous';
                            }
                            ?>
                            <span style="margin-left: 1rem;">
                                <i class="fas fa-calendar"></i> <?php echo formatDate($story['published_at']); ?>
                            </span>
                        </p>
                        
                        <p><?php echo htmlspecialchars(substr($story['story'], 0, 200)) . '...'; ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                            <a href="/public/story.php?id=<?php echo $story['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-book-open"></i> Read More
                            </a>
                            <span class="text-muted" style="font-size: 0.9rem;">
                                <i class="fas fa-eye"></i> <?php echo number_format($story['views']); ?> views
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-book-open" style="font-size: 5rem; color: var(--light-color); margin-bottom: 1rem;"></i>
                <h3>No Stories Yet</h3>
                <p>Be the first to share an inspiring story!</p>
                <?php if (Auth::isLoggedIn()): ?>
                    <button onclick="openModal('submitStoryModal')" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Share Your Story
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Submit Story Modal -->
<?php if (Auth::isLoggedIn()): ?>
<div id="submitStoryModal" class="modal">
    <div class="modal-content modal-3d" style="max-width: 600px;">
        <span class="modal-close" onclick="closeModal('submitStoryModal')">&times;</span>
        <h2><i class="fas fa-book"></i> Share Your Story</h2>
        <form id="submitStoryForm" onsubmit="handleSubmitStory(event)">
            <div class="form-group">
                <label for="storyTitle">Title</label>
                <input type="text" id="storyTitle" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="storyContent">Your Story</label>
                <textarea id="storyContent" name="story" rows="10" required 
                          placeholder="Share your journey, challenges overcome, and hope for others..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="storyMedia">Upload Photo/Video (Optional)</label>
                <input type="file" id="storyMedia" name="media" accept="image/*,video/*">
                <small>Max file size: 5MB</small>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="anonymous">
                    Submit anonymously
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-paper-plane"></i> Submit Story
                </button>
            </div>
        </form>
    </div>
</div>

<script>
async function handleSubmitStory(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/stories.php?action=submit', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
            closeModal('submitStoryModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
