<?php
$pageTitle = 'Outreach Map - OUTSINC';
require_once __DIR__ . '/../includes/header.php';

$db = getDB();
$conn = $db->getConnection();

// Get all active resources with coordinates
$resources = $conn->query("
    SELECT * FROM resources 
    WHERE is_active = 1 
    AND latitude IS NOT NULL 
    AND longitude IS NOT NULL
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-map"></i> Interactive Outreach Map</h1>
            <p>Visualize all available resources and services in your area</p>
        </div>
    </div>

    <!-- Map Container -->
    <div class="card mb-4">
        <div id="map" style="height: 600px; border-radius: 8px; position: relative;">
            <!-- Placeholder map content -->
            <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                <i class="fas fa-map-marked-alt" style="font-size: 6rem; color: var(--primary-color); margin-bottom: 2rem; animation: pulse 2s infinite;"></i>
                <h2>Interactive Map</h2>
                <p class="text-muted">Google Maps integration will display all resources here</p>
                <div style="margin-top: 2rem;">
                    <a href="/public/directory.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> View List Instead
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> Map Legend</h3>
        </div>
        <div class="card-body">
            <div class="grid grid-4">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-home" style="color: #3498db; font-size: 1.5rem;"></i>
                    <span>Shelter</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-utensils" style="color: #e74c3c; font-size: 1.5rem;"></i>
                    <span>Food</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-hospital" style="color: #2ecc71; font-size: 1.5rem;"></i>
                    <span>Medical</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-brain" style="color: #9b59b6; font-size: 1.5rem;"></i>
                    <span>Mental Health</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-hand-holding-medical" style="color: #f39c12; font-size: 1.5rem;"></i>
                    <span>Substance Abuse</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-balance-scale" style="color: #34495e; font-size: 1.5rem;"></i>
                    <span>Legal</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-briefcase" style="color: #16a085; font-size: 1.5rem;"></i>
                    <span>Employment</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-ellipsis-h" style="color: #95a5a6; font-size: 1.5rem;"></i>
                    <span>Other</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Resources Summary -->
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-list"></i> Resources Summary</h3>
        </div>
        <div class="card-body">
            <p><strong><?php echo count($resources); ?></strong> resources available on the map</p>
            <div style="margin-top: 1rem;">
                <a href="/public/directory.php" class="btn btn-primary">
                    <i class="fas fa-th-list"></i> View Detailed List
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}
</style>

<script>
// Resource data for future map integration
const resources = <?php echo json_encode($resources); ?>;

// Google Maps integration would go here
// function initMap() { ... }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
