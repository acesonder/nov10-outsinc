    </div> <!-- End content-wrapper -->

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-section">
                    <h3><i class="fas fa-hands-helping"></i> OUTSINC</h3>
                    <p>Outreach Someone In Need of Change</p>
                    <p>Connecting those in need with resources, support, and community.</p>
                    <div class="social-links">
                        <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" title="LinkedIn"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/public/index.php">Home</a></li>
                        <li><a href="/public/directory.php">Service Directory</a></li>
                        <li><a href="/public/map.php">Outreach Map</a></li>
                        <li><a href="/public/stories.php">Success Stories</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Get Involved</h4>
                    <ul>
                        <li><a href="/public/events.php">Volunteer Events</a></li>
                        <li><a href="#" onclick="openModal('requestHelpModal')">Request Help</a></li>
                        <li><a href="/public/contact.php">Contact Us</a></li>
                        <li><a href="/public/about.php">About Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="/public/faq.php">FAQ</a></li>
                        <li><a href="/public/privacy.php">Privacy Policy</a></li>
                        <li><a href="/public/terms.php">Terms of Service</a></li>
                        <li><a href="/public/accessibility.php">Accessibility</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> OUTSINC. All rights reserved.</p>
                <p>Made with <i class="fas fa-heart"></i> for those in need</p>
            </div>
        </div>
    </footer>

    <!-- Login Modal -->
    <div id="loginModal" class="modal">
        <div class="modal-content modal-3d">
            <span class="modal-close" onclick="closeModal('loginModal')">&times;</span>
            <h2><i class="fas fa-sign-in-alt"></i> Login</h2>
            <form id="loginForm" onsubmit="handleLogin(event)">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
                <div class="form-footer">
                    <a href="#" onclick="closeModal('loginModal'); openModal('resetPasswordModal');">Forgot password?</a>
                    <a href="#" onclick="closeModal('loginModal'); openModal('registerModal');">Need an account?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Register Modal -->
    <div id="registerModal" class="modal">
        <div class="modal-content modal-3d">
            <span class="modal-close" onclick="closeModal('registerModal')">&times;</span>
            <h2><i class="fas fa-user-plus"></i> Register</h2>
            <form id="registerForm" onsubmit="handleRegister(event)">
                <div class="form-row">
                    <div class="form-group">
                        <label for="regFirstName">First Name</label>
                        <input type="text" id="regFirstName" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="regLastName">Last Name</label>
                        <input type="text" id="regLastName" name="last_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="regEmail">Email</label>
                    <input type="email" id="regEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="regPassword">Password</label>
                    <input type="password" id="regPassword" name="password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="regConfirmPassword">Confirm Password</label>
                    <input type="password" id="regConfirmPassword" name="confirm_password" minlength="6" required>
                </div>
                <div class="form-group">
                    <label for="regSecurityQuestion">Security Question</label>
                    <select id="regSecurityQuestion" name="security_question" required>
                        <option value="">Select a question...</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                        <option value="What city were you born in?">What city were you born in?</option>
                        <option value="What is your favorite color?">What is your favorite color?</option>
                        <option value="What was your childhood nickname?">What was your childhood nickname?</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="regSecurityAnswer">Security Answer</label>
                    <input type="text" id="regSecurityAnswer" name="security_answer" required>
                </div>
                <div class="form-group">
                    <label for="regRole">I am registering as:</label>
                    <select id="regRole" name="role" required>
                        <option value="recipient">Someone seeking help</option>
                        <option value="volunteer">Volunteer</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </div>
                <div class="form-footer">
                    <a href="#" onclick="closeModal('registerModal'); openModal('loginModal');">Already have an account?</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div id="resetPasswordModal" class="modal">
        <div class="modal-content modal-3d">
            <span class="modal-close" onclick="closeModal('resetPasswordModal')">&times;</span>
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            <form id="resetPasswordForm" onsubmit="handleResetPassword(event)">
                <div class="form-group">
                    <label for="resetEmail">Email</label>
                    <input type="email" id="resetEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="resetSecurityAnswer">Security Answer</label>
                    <input type="text" id="resetSecurityAnswer" name="security_answer" required>
                    <small>Answer to your security question</small>
                </div>
                <div class="form-group">
                    <label for="resetNewPassword">New Password</label>
                    <input type="password" id="resetNewPassword" name="new_password" minlength="6" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-key"></i> Reset Password
                    </button>
                </div>
                <div class="form-footer">
                    <a href="#" onclick="closeModal('resetPasswordModal'); openModal('loginModal');">Back to login</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Request Help Modal -->
    <div id="requestHelpModal" class="modal">
        <div class="modal-content modal-3d">
            <span class="modal-close" onclick="closeModal('requestHelpModal')">&times;</span>
            <h2><i class="fas fa-hand-holding-heart"></i> Request Help</h2>
            <form id="requestHelpForm" onsubmit="handleRequestHelp(event)">
                <div class="form-group">
                    <label for="helpTitle">Brief Description</label>
                    <input type="text" id="helpTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="helpType">Type of Help Needed</label>
                    <select id="helpType" name="type" required>
                        <option value="">Select type...</option>
                        <option value="housing">Housing/Shelter</option>
                        <option value="food">Food</option>
                        <option value="medical">Medical Care</option>
                        <option value="mental_health">Mental Health</option>
                        <option value="substance_abuse">Substance Abuse Support</option>
                        <option value="legal">Legal Assistance</option>
                        <option value="employment">Employment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="helpDescription">Details</label>
                    <textarea id="helpDescription" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="helpPriority">Priority</label>
                    <select id="helpPriority" name="priority" required>
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="helpAnonymous" name="is_anonymous">
                        Submit anonymously
                    </label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="notification-toast"></div>

    <!-- Audio for notifications -->
    <audio id="notificationSound" src="/assets/sounds/notification.mp3" preload="auto"></audio>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="/public/js/main.js"></script>
    <?php if (isset($additionalJS)) echo $additionalJS; ?>
</body>
</html>
