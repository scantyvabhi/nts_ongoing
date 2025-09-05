# NTS Employee Management System

This system provides a complete employee management solution with an admin panel for managing employee data and dynamic display on the website.

## Features

### Admin Panel (`admin.html`)
- **Tabular Interface**: View all employees in a clean, sortable table
- **Add New Employees**: Complete form with all employee details
- **Edit Employees**: Modify existing employee information
- **Delete Employees**: Remove employees with confirmation
- **Search & Filter**: Search by name, designation, or employee ID
- **Department Filter**: Filter employees by department
- **Export CSV**: Download current employee data as CSV
- **Real-time Updates**: Changes are immediately reflected on the website

### Dynamic Website Integration
- **aboutus.html**: Automatically loads employee cards from CSV data
- **employee.html**: Individual employee profile pages with dynamic data
- **Real-time Sync**: Changes made in admin panel immediately appear on the website

## File Structure

```
NTS-Website/
├── admin.html              # Admin panel interface
├── aboutus.html            # Team page (dynamically loads from CSV)
├── employee.html           # Individual employee profiles
├── update_csv.php          # Backend for CSV operations
├── data/
│   └── employees.csv       # Employee database
└── assets/                 # Images and other assets
```

## Database Schema (CSV)

The `employees.csv` file contains the following columns:

| Column | Description | Required |
|--------|-------------|----------|
| id | Unique identifier | Yes |
| name | Display name | Yes |
| fullName | Complete name | Yes |
| designation | Job title | Yes |
| department | Department | Yes |
| employeeId | Employee ID (e.g., NTS001) | Yes |
| doj | Date of joining | Yes |
| workingPeriod | Working duration | Yes |
| email | Email address | Yes |
| phone | Phone number | Yes |
| image | Image path | Yes |
| about | Description | Yes |
| linkedin | LinkedIn URL | No |
| instagram | Instagram URL | No |
| isActive | Active status (1/0) | Yes |

## Setup Instructions

### 1. Server Requirements
- Web server with PHP support (Apache, Nginx, etc.)
- PHP 7.0 or higher
- File write permissions for the `data/` directory

### 2. Installation
1. Upload all files to your web server
2. Ensure the `data/` directory has write permissions (chmod 755)
3. Access `admin.html` to start managing employees

### 3. Usage

#### Adding a New Employee
1. Open `admin.html`
2. Click "Add New Employee"
3. Fill in all required fields
4. Click "Save Employee"

#### Editing an Employee
1. Find the employee in the table
2. Click the edit (pencil) icon
3. Modify the information
4. Click "Save Employee"

#### Deleting an Employee
1. Find the employee in the table
2. Click the delete (trash) icon
3. Confirm deletion

#### Exporting Data
- Click "Export CSV" to download current employee data

## Technical Details

### Frontend Technologies
- **HTML5**: Structure
- **Tailwind CSS**: Styling
- **Vanilla JavaScript**: Functionality
- **Papa Parse**: CSV parsing and generation

### Backend Technologies
- **PHP**: Server-side processing
- **CSV**: Data storage format

### Data Flow
1. Admin makes changes in `admin.html`
2. JavaScript sends data to `update_csv.php`
3. PHP updates `employees.csv`
4. Website pages load data from CSV
5. Changes are immediately visible

## Security Considerations

### Current Implementation
- Basic file-based storage
- No authentication (add as needed)
- Direct file access to CSV

### Recommended Enhancements
- Add admin authentication
- Implement input validation
- Add CSRF protection
- Use database instead of CSV for production
- Add image upload functionality
- Implement backup system

## Troubleshooting

### Common Issues

1. **CSV not updating**
   - Check file permissions on `data/` directory
   - Ensure PHP has write access
   - Check server error logs

2. **Images not loading**
   - Verify image paths in CSV
   - Ensure images exist in `assets/` directory
   - Check file permissions

3. **Admin panel not working**
   - Ensure PHP is enabled on server
   - Check browser console for JavaScript errors
   - Verify all files are uploaded correctly

### Fallback System
- If CSV loading fails, the system falls back to hardcoded data
- Admin panel can still export CSV for manual updates
- All functionality remains available

## Customization

### Adding New Fields
1. Update the CSV schema
2. Modify admin form in `admin.html`
3. Update display logic in `aboutus.html` and `employee.html`
4. Update PHP backend if needed

### Styling Changes
- Modify Tailwind classes in HTML files
- Update CSS in `style.css`
- Customize neumorphic design elements

### Department Management
- Add new departments to the dropdown in admin form
- Update filter options in admin table
- Modify department logic in `aboutus.html`

## Support

For technical support or feature requests, please contact the development team.

---

**Note**: This system is designed for small to medium-sized organizations. For larger deployments, consider implementing a proper database system with enhanced security features. 