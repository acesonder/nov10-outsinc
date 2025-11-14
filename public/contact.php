<?php
$pageTitle = 'Contact Us - OUTSINC';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1><i class="fas fa-envelope"></i> Contact Us</h1>
            <p>We're here to help. Reach out to us anytime.</p>
        </div>
    </div>

    <div class="grid grid-2">
        <!-- Contact Form -->
        <div class="card">
            <div class="card-header">
                <h2>Send us a Message</h2>
            </div>
            <div class="card-body">
                <form id="contactForm" onsubmit="handleContactForm(event)">
                    <div class="form-group">
                        <label for="contactName">Name</label>
                        <input type="text" id="contactName" name="name" required 
                               value="<?php echo $isLoggedIn ? htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contactEmail">Email</label>
                        <input type="email" id="contactEmail" name="email" required
                               value="<?php echo $isLoggedIn ? htmlspecialchars($currentUser['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="contactSubject">Subject</label>
                        <input type="text" id="contactSubject" name="subject" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactMessage">Message</label>
                        <textarea id="contactMessage" name="message" rows="6" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>

        <!-- Contact Information -->
        <div>
            <div class="card mb-3">
                <div class="card-header">
                    <h2>Get in Touch</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div>
                            <h4><i class="fas fa-phone"></i> Phone</h4>
                            <p><a href="tel:+1234567890">(123) 456-7890</a></p>
                        </div>
                        
                        <div>
                            <h4><i class="fas fa-envelope"></i> Email</h4>
                            <p><a href="mailto:info@outsinc.org">info@outsinc.org</a></p>
                        </div>
                        
                        <div>
                            <h4><i class="fas fa-map-marker-alt"></i> Address</h4>
                            <p>123 Community Street<br>
                            Your City, ST 12345<br>
                            United States</p>
                        </div>
                        
                        <div>
                            <h4><i class="fas fa-clock"></i> Hours</h4>
                            <p>Monday - Friday: 9:00 AM - 5:00 PM<br>
                            Saturday: 10:00 AM - 2:00 PM<br>
                            Sunday: Closed</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Emergency Resources</h2>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="padding: 1rem; background: var(--danger-color); color: white; border-radius: 8px;">
                            <h4>Emergency: 911</h4>
                            <p style="margin: 0;">For immediate life-threatening emergencies</p>
                        </div>
                        
                        <div style="padding: 1rem; background: var(--warning-color); color: white; border-radius: 8px;">
                            <h4>Crisis Hotline: 988</h4>
                            <p style="margin: 0;">24/7 Suicide & Crisis Lifeline</p>
                        </div>
                        
                        <div style="padding: 1rem; background: var(--info-color); color: white; border-radius: 8px;">
                            <h4>National Helpline: 1-800-662-4357</h4>
                            <p style="margin: 0;">SAMHSA's National Helpline (24/7)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="card mt-4 mb-4">
        <div class="card-header">
            <h2><i class="fas fa-question-circle"></i> Frequently Asked Questions</h2>
        </div>
        <div class="card-body">
            <div class="accordion">
                <div class="accordion-item">
                    <h3 class="accordion-header" onclick="toggleAccordion(this)">
                        <i class="fas fa-chevron-down"></i> How do I request help?
                    </h3>
                    <div class="accordion-content">
                        <p>You can request help by clicking the "Request Help" button on the homepage or by registering for an account. You can also submit requests anonymously if you prefer.</p>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header" onclick="toggleAccordion(this)">
                        <i class="fas fa-chevron-down"></i> Is my information kept confidential?
                    </h3>
                    <div class="accordion-content">
                        <p>Yes, all your information is kept strictly confidential. We use encryption for all messages and personal data. You also have the option to submit help requests anonymously.</p>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header" onclick="toggleAccordion(this)">
                        <i class="fas fa-chevron-down"></i> How can I volunteer?
                    </h3>
                    <div class="accordion-content">
                        <p>Register for an account and select "Volunteer" as your role. Once registered, you can browse volunteer opportunities, RSVP to events, and help people in need through our case management system.</p>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h3 class="accordion-header" onclick="toggleAccordion(this)">
                        <i class="fas fa-chevron-down"></i> What types of resources are available?
                    </h3>
                    <div class="accordion-content">
                        <p>We provide access to shelters, food banks, medical clinics, mental health services, substance abuse support, legal assistance, employment resources, and more. Browse our Service Directory to find what you need.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.accordion-item {
    border-bottom: 1px solid var(--light-color);
    padding: 1rem 0;
}

.accordion-header {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 1.1rem;
    font-weight: 600;
    transition: var(--transition);
}

.accordion-header:hover {
    color: var(--primary-color);
}

.accordion-header i {
    transition: var(--transition);
}

.accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    padding-left: 2rem;
}

.accordion-content.active {
    max-height: 500px;
    margin-top: 1rem;
}

.accordion-item.active .accordion-header i {
    transform: rotate(180deg);
}
</style>

<script>
function toggleAccordion(element) {
    const item = element.parentElement;
    const content = element.nextElementSibling;
    
    item.classList.toggle('active');
    content.classList.toggle('active');
}

async function handleContactForm(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('/api/contact.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification(data.message, 'success');
            form.reset();
        } else {
            showNotification(data.message, 'error');
        }
    } catch (error) {
        showNotification('An error occurred. Please try again.', 'error');
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
