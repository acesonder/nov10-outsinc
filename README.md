# OUTSINC - Outreach Someone In Need of Change

A comprehensive web application dedicated to helping people experiencing homelessness, mental health issues, or substance use challenges. OUTSINC connects those in need with resources, support, volunteers, and the broader community.

## Features

### User Management
- **Multi-role system**: Recipients, Volunteers, Staff, and Admins
- **Secure authentication**: Email-based login with security question password reset
- **Role-based access control**: Different dashboards and permissions for each role

### Core Functionality
- **Service Directory**: Comprehensive listing of shelters, food banks, clinics, and support services
- **Interactive Map**: Visual representation of all resources and services
- **Case Management**: Request help and track status from submission to resolution
- **Event System**: Volunteer opportunities and community events with RSVP functionality
- **Success Stories**: Share and read inspiring stories of hope and transformation
- **Real-time Notifications**: In-app notifications with custom sounds
- **Messaging System**: Secure communication between users

### Advanced Features
- **Gamification**: Earn badges and achievements for participation
- **Favorites System**: Save frequently used resources
- **Analytics Dashboard**: Track platform usage and impact
- **Admin Panel**: Full CRUD operations for all resources
- **Audit Logging**: Complete activity tracking for security
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **3D UI Elements**: Modern, engaging interface with depth and animations

## Technology Stack

- **Backend**: PHP 7.4+, MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Libraries**: 
  - jQuery 3.6
  - Font Awesome 6.4
  - DataTables 1.13
  - Chart.js (for analytics)
- **APIs**: Google Maps API (for map features)

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for future extensions)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/acesonder/nov10-outsinc.git
   cd nov10-outsinc
   ```

2. **Configure the database**
   - Create a MySQL database named `outsinc`
   - Import the schema:
   ```bash
   mysql -u root -p outsinc < database/schema.sql
   ```

3. **Configure the application**
   - Edit `includes/config.php` with your database credentials
   - Update `DB_HOST`, `DB_USER`, `DB_PASS` if needed
   - Set `SITE_URL` to your domain

4. **Set permissions**
   ```bash
   chmod -R 755 public/
   chmod -R 777 public/uploads/
   ```

5. **Access the application**
   - Point your web server to the `public/` directory
   - Navigate to `http://localhost/` (or your domain)

### Default Admin Account
- **Email**: admin@outsinc.org
- **Password**: admin123
- **Important**: Change this password immediately after first login!

## Project Structure

```
outsinc/
├── admin/              # Admin panel pages
├── api/                # API endpoints
│   ├── admin/          # Admin-specific APIs
│   ├── auth.php        # Authentication
│   ├── cases.php       # Case management
│   ├── events.php      # Event management
│   ├── favorites.php   # Favorites system
│   ├── messages.php    # Messaging
│   ├── notifications.php # Notifications
│   └── stories.php     # Success stories
├── assets/             # Static assets
│   ├── icons/          # Custom icons
│   └── sounds/         # Notification sounds
├── database/           # Database schema
├── includes/           # PHP includes
│   ├── auth.php        # Authentication logic
│   ├── config.php      # Configuration
│   ├── db.php          # Database connection
│   ├── functions.php   # Helper functions
│   ├── header.php      # Common header
│   └── footer.php      # Common footer
└── public/             # Public web root
    ├── css/            # Stylesheets
    ├── js/             # JavaScript files
    ├── uploads/        # User uploads
    └── *.php           # Page files
```

## Usage

### For Recipients (Those Seeking Help)
1. Register for an account or browse resources anonymously
2. Search the service directory for needed resources
3. Request help through the dashboard
4. Track the status of your requests
5. Save favorite resources for quick access
6. Attend community events

### For Volunteers
1. Register as a volunteer
2. Browse and RSVP to volunteer events
3. Get assigned to help requests
4. Track your impact and earn badges
5. Communicate with recipients and staff

### For Staff/Admins
1. Access the admin panel
2. Manage users, resources, and events
3. Review and publish success stories
4. Monitor platform activity through audit logs
5. Create announcements and banners
6. View analytics and generate reports

## Security Features

- Password hashing with bcrypt
- Session-based authentication
- CSRF protection
- Input sanitization and validation
- SQL injection prevention with prepared statements
- Role-based access control
- Encrypted message storage
- Complete audit logging

## Contributing

This is a community-focused project. Contributions are welcome!

## License

This project is open source and available under the MIT License.

## Support

For support or questions:
- Email: info@outsinc.org
- Phone: (123) 456-7890

## Acknowledgments

Built with care for those in need. Every feature is designed with empathy and the goal of making a real difference in people's lives.