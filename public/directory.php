<?php
$pageTitle = 'Service Directory - OUTSINC';
$additionalCSS = '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">';
$additionalJS = '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script><script src="/public/js/directory.js"></script>';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conn = $db->getConnection();

// Get filter parameters
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM resources WHERE is_active = 1";
$params = [];

if (!empty($type)) {
    $query .= " AND type = ?";
    $params[] = $type;
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR description LIKE ? OR address LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$query .= " ORDER BY name ASC";

// Execute query
if (!empty($params)) {
    $stmt = $conn->prepare($query);
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $resources = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $resources = $conn->query($query)->fetch_all(MYSQLI_ASSOC);
}

// Get user favorites if logged in
$favorites = [];
if (Auth::isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $favResult = $conn->query("SELECT resource_id FROM favorites WHERE user_id = $userId");
    while ($row = $favResult->fetch_assoc()) {
        $favorites[] = $row['resource_id'];
    }
}
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-map-marked-alt"></i> Service Directory</h1>
            <p>Find shelters, food banks, clinics, and other essential services</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <input type="text" name="search" placeholder="Search by name, description, or address..." 
                           value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="shelter" <?php echo $type === 'shelter' ? 'selected' : ''; ?>>Shelter</option>
                        <option value="food" <?php echo $type === 'food' ? 'selected' : ''; ?>>Food</option>
                        <option value="medical" <?php echo $type === 'medical' ? 'selected' : ''; ?>>Medical</option>
                        <option value="mental_health" <?php echo $type === 'mental_health' ? 'selected' : ''; ?>>Mental Health</option>
                        <option value="substance_abuse" <?php echo $type === 'substance_abuse' ? 'selected' : ''; ?>>Substance Abuse</option>
                        <option value="legal" <?php echo $type === 'legal' ? 'selected' : ''; ?>>Legal</option>
                        <option value="employment" <?php echo $type === 'employment' ? 'selected' : ''; ?>>Employment</option>
                        <option value="other" <?php echo $type === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>

    <!-- View Toggle -->
    <div class="card mb-4">
        <div class="card-body" style="padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p style="margin: 0;"><strong><?php echo count($resources); ?></strong> resources found</p>
                <div style="display: flex; gap: 0.5rem;">
                    <button class="btn btn-primary" onclick="showView('list')" id="listViewBtn">
                        <i class="fas fa-list"></i> List
                    </button>
                    <button class="btn btn-outline" onclick="showView('map')" id="mapViewBtn">
                        <i class="fas fa-map"></i> Map
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="listView">
        <div class="grid grid-2">
            <?php foreach ($resources as $resource): ?>
                <div class="card">
                    <div class="card-header" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <h3 style="margin: 0; flex: 1;"><?php echo htmlspecialchars($resource['name']); ?></h3>
                            <?php if (Auth::isLoggedIn()): ?>
                                <button class="btn btn-sm favorite-btn" 
                                        data-resource-id="<?php echo $resource['id']; ?>"
                                        onclick="toggleFavorite(<?php echo $resource['id']; ?>)">
                                    <i class="fas fa-heart <?php echo in_array($resource['id'], $favorites) ? '' : 'far'; ?>"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        <span class="badge badge-primary" style="margin-top: 0.5rem;">
                            <?php echo ucfirst(str_replace('_', ' ', $resource['type'])); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if ($resource['description']): ?>
                            <p><?php echo htmlspecialchars($resource['description']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($resource['address']): ?>
                            <p><i class="fas fa-map-marker-alt"></i> 
                                <?php echo htmlspecialchars($resource['address'] . ', ' . $resource['city'] . ', ' . $resource['state'] . ' ' . $resource['zip']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($resource['phone']): ?>
                            <p><i class="fas fa-phone"></i> 
                                <a href="tel:<?php echo htmlspecialchars($resource['phone']); ?>">
                                    <?php echo htmlspecialchars($resource['phone']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($resource['email']): ?>
                            <p><i class="fas fa-envelope"></i> 
                                <a href="mailto:<?php echo htmlspecialchars($resource['email']); ?>">
                                    <?php echo htmlspecialchars($resource['email']); ?>
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($resource['website']): ?>
                            <p><i class="fas fa-globe"></i> 
                                <a href="<?php echo htmlspecialchars($resource['website']); ?>" target="_blank" rel="noopener">
                                    Visit Website
                                </a>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($resource['hours']): ?>
                            <p><i class="fas fa-clock"></i> <?php echo htmlspecialchars($resource['hours']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($resource['accessibility_features']): ?>
                            <p><i class="fas fa-wheelchair"></i> <?php echo htmlspecialchars($resource['accessibility_features']); ?></p>
                        <?php endif; ?>
                        
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                            <?php if ($resource['latitude'] && $resource['longitude']): ?>
                                <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $resource['latitude']; ?>,<?php echo $resource['longitude']; ?>" 
                                   target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-directions"></i> Get Directions
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($resources) === 0): ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-search" style="font-size: 4rem; color: var(--light-color); margin-bottom: 1rem;"></i>
                    <h3>No resources found</h3>
                    <p>Try adjusting your search filters</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Map View -->
    <div id="mapView" style="display: none;">
        <div class="card">
            <div id="map" style="height: 600px; border-radius: 8px;"></div>
        </div>
    </div>
</div>

<script>
const resources = <?php echo json_encode($resources); ?>;
const favorites = <?php echo json_encode($favorites); ?>;

function showView(view) {
    if (view === 'list') {
        document.getElementById('listView').style.display = 'block';
        document.getElementById('mapView').style.display = 'none';
        document.getElementById('listViewBtn').classList.add('btn-primary');
        document.getElementById('listViewBtn').classList.remove('btn-outline');
        document.getElementById('mapViewBtn').classList.remove('btn-primary');
        document.getElementById('mapViewBtn').classList.add('btn-outline');
    } else {
        document.getElementById('listView').style.display = 'none';
        document.getElementById('mapView').style.display = 'block';
        document.getElementById('mapViewBtn').classList.add('btn-primary');
        document.getElementById('mapViewBtn').classList.remove('btn-outline');
        document.getElementById('listViewBtn').classList.remove('btn-primary');
        document.getElementById('listViewBtn').classList.add('btn-outline');
        initMap();
    }
}

async function toggleFavorite(resourceId) {
    try {
        const formData = new FormData();
        formData.append('resource_id', resourceId);
        
        const response = await fetch('/api/favorites.php?action=toggle', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Toggle heart icon
            const btn = document.querySelector(`[data-resource-id="${resourceId}"] i`);
            btn.classList.toggle('far');
            btn.classList.toggle('fas');
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred', 'error');
    }
}

function initMap() {
    // This will be implemented with Google Maps API
    const mapDiv = document.getElementById('map');
    mapDiv.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);">' +
        '<div style="text-align: center;">' +
        '<i class="fas fa-map" style="font-size: 4rem; margin-bottom: 1rem;"></i>' +
        '<h3>Map View</h3>' +
        '<p>Google Maps integration will be added here</p>' +
        '</div></div>';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
