<?php
$pageTitle = 'Manage Resources - Admin - OUTSINC';
$additionalCSS = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">';
$additionalJS = '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>';
require_once __DIR__ . '/../includes/header.php';

Auth::requireRole('admin');

$db = getDB();
$conn = $db->getConnection();

// Get all resources
$resources = $conn->query("SELECT * FROM resources ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-map-marker-alt"></i> Manage Resources</h1>
            <button onclick="openModal('addResourceModal')" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Resource
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div style="overflow-x: auto;">
                <table id="resourcesTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resources as $resource): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($resource['name']); ?></td>
                                <td><span class="badge badge-primary"><?php echo ucfirst(str_replace('_', ' ', $resource['type'])); ?></span></td>
                                <td><?php echo htmlspecialchars($resource['city'] . ', ' . $resource['state']); ?></td>
                                <td><?php echo htmlspecialchars($resource['phone']); ?></td>
                                <td>
                                    <?php if ($resource['is_active']): ?>
                                        <span class="badge badge-secondary">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="editResource(<?php echo $resource['id']; ?>)" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteResource(<?php echo $resource['id']; ?>)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Resource Modal -->
<div id="addResourceModal" class="modal">
    <div class="modal-content modal-3d" style="max-width: 700px;">
        <span class="modal-close" onclick="closeModal('addResourceModal')">&times;</span>
        <h2><i class="fas fa-plus"></i> Add New Resource</h2>
        <form id="addResourceForm" onsubmit="handleAddResource(event)">
            <div class="form-row">
                <div class="form-group">
                    <label for="resourceName">Name *</label>
                    <input type="text" id="resourceName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="resourceType">Type *</label>
                    <select id="resourceType" name="type" required>
                        <option value="">Select type...</option>
                        <option value="shelter">Shelter</option>
                        <option value="food">Food</option>
                        <option value="medical">Medical</option>
                        <option value="mental_health">Mental Health</option>
                        <option value="substance_abuse">Substance Abuse</option>
                        <option value="legal">Legal</option>
                        <option value="employment">Employment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="resourceDescription">Description</label>
                <textarea id="resourceDescription" name="description" rows="3"></textarea>
            </div>
            
            <div class="form-group">
                <label for="resourceAddress">Address</label>
                <input type="text" id="resourceAddress" name="address">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="resourceCity">City</label>
                    <input type="text" id="resourceCity" name="city">
                </div>
                <div class="form-group">
                    <label for="resourceState">State</label>
                    <input type="text" id="resourceState" name="state">
                </div>
                <div class="form-group">
                    <label for="resourceZip">ZIP</label>
                    <input type="text" id="resourceZip" name="zip">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="resourcePhone">Phone</label>
                    <input type="tel" id="resourcePhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="resourceEmail">Email</label>
                    <input type="email" id="resourceEmail" name="email">
                </div>
            </div>
            
            <div class="form-group">
                <label for="resourceWebsite">Website</label>
                <input type="url" id="resourceWebsite" name="website">
            </div>
            
            <div class="form-group">
                <label for="resourceHours">Hours</label>
                <input type="text" id="resourceHours" name="hours" placeholder="e.g., Mon-Fri 9AM-5PM">
            </div>
            
            <div class="form-group">
                <label for="resourceAccessibility">Accessibility Features</label>
                <input type="text" id="resourceAccessibility" name="accessibility_features">
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-save"></i> Save Resource
                </button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#resourcesTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25
    });
});

async function handleAddResource(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/admin/resources.php?action=create', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
            closeModal('addResourceModal');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}

async function deleteResource(id) {
    if (!confirm('Are you sure you want to delete this resource?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('id', id);
        
        const response = await fetch('/api/admin/resources.php?action=delete', {
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
