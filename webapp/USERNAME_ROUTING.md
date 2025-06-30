# Username-Based Dashboard Routing

This document explains the new username-based routing system implemented in the VAL Edu webapp.

## Overview

Instead of using role-based dashboard routes like `/webapp/admin/dashboard`, `/webapp/tutor/dashboard`, etc., the system now uses username-based routes in the format `/webapp/{username}`.

## How It Works

### 1. User Login Process
When a user successfully logs in:
- The system stores their username in `$_SESSION['username']`
- Upon successful authentication, they are redirected to `/webapp/{their_username}`

### 2. Route Handling
The main routing logic in `index.php` handles username routes as follows:

```php
// Pattern matching for username routes: /webapp/{username}
if (preg_match('/^\/([a-zA-Z0-9_-]+)$/', $path, $matches)) {
    $username = $matches[1];
    
    // Security check: user must be logged in and accessing their own dashboard
    if (isset($_SESSION['user_id']) && $_SESSION['username'] === $username) {
        // Route to appropriate controller based on user role
        switch ($_SESSION['user_role']) {
            case 'admin': // AdminController->dashboard()
            case 'tutor': // TutorController->dashboard() 
            case 'student': // StudentController->dashboard()
            case 'parent': // ParentController->dashboard()
        }
    } else {
        // Redirect to login if unauthorized
        header('Location: /webapp/login');
    }
}
```

### 3. Security Features
- **Authentication Required**: Users must be logged in to access any dashboard
- **Authorization Check**: Users can only access their own dashboard (username must match session)
- **Role-Based Content**: The appropriate controller is loaded based on the user's role
- **Backward Compatibility**: Old role-based routes redirect to new username routes

### 4. Examples

| User | Username | Role | Dashboard URL |
|------|----------|------|---------------|
| John Doe | `johndoe` | student | `/webapp/johndoe` |
| Jane Smith | `jane.smith` | tutor | `/webapp/jane.smith` |
| Admin User | `admin123` | admin | `/webapp/admin123` |
| Parent User | `parent_mary` | parent | `/webapp/parent_mary` |

### 5. Updated Components

#### Controllers
- **AdminController**: Handles admin dashboard at `/webapp/{admin_username}`
- **TutorController**: Handles tutor dashboard at `/webapp/{tutor_username}`
- **StudentController**: Handles student dashboard at `/webapp/{student_username}`
- **ParentController**: Handles parent dashboard at `/webapp/{parent_username}`

#### Views
- **HomeHeader.php**: Updated to show username-based dashboard link
- Dashboard navigation now uses: `<a href="/webapp/<?= urlencode($_SESSION['username']) ?>">Dashboard</a>`

#### Authentication
- **AuthController**: Updated `redirectToDashboard()` method to redirect to username routes
- Login process now redirects to `/webapp/{username}` instead of role-based routes

### 6. Benefits

1. **Personalized URLs**: Each user has their own unique dashboard URL
2. **Better Security**: URL-based access control prevents users from accessing other dashboards
3. **Cleaner Architecture**: Single routing pattern for all user types
4. **SEO Friendly**: More intuitive and user-friendly URLs
5. **Scalable**: Easy to extend for additional user roles

### 7. Migration Notes

- Old role-based routes (`/admin/dashboard`, `/tutor/dashboard`, etc.) are automatically redirected to new username routes
- No changes required for existing user accounts
- Session management remains the same

## Testing

To test the new routing:

1. Register/Login as any user
2. Note the redirect to `/webapp/{your_username}`
3. Verify that only your own dashboard is accessible
4. Try accessing another user's dashboard URL (should redirect to login)
5. Test old role-based URLs (should redirect to new format)
