# Peanut Connect - Developer Documentation

## Architecture Overview

Peanut Connect is a WordPress plugin that enables remote site monitoring and management. It consists of a PHP backend that integrates with WordPress and a React frontend for the admin interface.

### Directory Structure

```
peanut-connect/
├── peanut-connect.php      # Main plugin file, WordPress integration
├── includes/               # PHP classes
│   ├── class-connect-api.php          # REST API endpoints
│   ├── class-connect-auth.php         # Authentication & permissions
│   ├── class-connect-health.php       # Site health data collection
│   ├── class-connect-updates.php      # Plugin/theme/core updates
│   ├── class-connect-error-log.php    # Error capture & logging
│   ├── class-connect-activity-log.php # Audit trail logging
│   ├── class-connect-rate-limiter.php # API rate limiting
│   └── class-connect-self-updater.php # Auto-update from GitHub
├── frontend/               # React SPA
│   ├── src/
│   │   ├── api/           # API client & endpoints
│   │   ├── components/    # Reusable UI components
│   │   ├── contexts/      # React contexts (theme)
│   │   ├── pages/         # Route pages
│   │   └── types/         # TypeScript definitions
│   └── vitest.config.ts   # Test configuration
├── tests/                  # PHPUnit tests
│   └── phpunit/
│       ├── bootstrap.php  # Test setup & WP mocks
│       └── unit/          # Unit test files
├── docs/                   # Documentation
│   ├── openapi.yaml       # API specification
│   └── DEVELOPMENT.md     # This file
└── scripts/               # Build & release scripts
```

## Backend Architecture

### Core Classes

#### Peanut_Connect_API
The main REST API controller. Registers all endpoints and handles routing.

**Key Endpoints:**
- `/settings` - Admin settings management
- `/health` - Site health data (manager endpoint)
- `/updates` - Available updates (manager endpoint)
- `/activity` - Activity log entries

#### Peanut_Connect_Auth
Handles authentication for manager-to-site communication.

**Authentication Flow:**
1. Manager sends `Authorization: Bearer <site_key>` header
2. Auth class validates key against stored `peanut_connect_site_key` option
3. Permissions are checked for the specific endpoint
4. Rate limiting is applied via `Peanut_Connect_Rate_Limiter`

```php
// Example: Checking permission
Peanut_Connect_Auth::has_permission('perform_updates');
```

#### Peanut_Connect_Health
Collects comprehensive health data about the WordPress installation.

**Collected Data:**
- WordPress version & update status
- PHP version & extension availability
- SSL certificate status & expiry
- Plugin/theme inventory & updates
- Disk space usage
- Database statistics
- File permissions
- Backup plugin detection

#### Peanut_Connect_Rate_Limiter
Prevents API abuse through per-endpoint rate limiting.

**Configuration:**
```php
private const LIMITS = [
    'auth' => ['requests' => 10, 'window' => 60],    // Auth: 10/min
    'health' => ['requests' => 30, 'window' => 60],  // Health: 30/min
    'default' => ['requests' => 60, 'window' => 60], // Default: 60/min
];
```

### Data Storage

| Option Name | Purpose |
|-------------|---------|
| `peanut_connect_site_key` | Bearer token for API auth |
| `peanut_connect_manager_url` | Connected manager URL |
| `peanut_connect_last_sync` | Last successful sync timestamp |
| `peanut_connect_permissions` | Feature permission flags |
| `peanut_connect_error_logging` | Error logging enabled flag |

### Logs

Logs are stored in `wp-content/peanut-logs/`:
- `error-log.json` - PHP error entries
- `activity-log.json` - Audit trail entries

Both are protected via `.htaccess` to prevent direct web access.

## Frontend Architecture

### Tech Stack
- **React 18** - UI library
- **TypeScript** - Type safety
- **Vite** - Build tool
- **TanStack Query** - Server state management
- **React Router** - Client-side routing
- **Tailwind CSS** - Styling
- **Lucide Icons** - Icon library

### State Management

**Server State:** Managed by TanStack Query
```tsx
const { data, isLoading, error } = useQuery({
  queryKey: ['settings'],
  queryFn: settingsApi.get,
});
```

**Client State:** React context for theme, local state for UI
```tsx
const { theme, toggleTheme } = useTheme();
```

### Component Patterns

**Common Components (`src/components/common/`):**
- `Button` - Variant-based button with loading state
- `Card`, `CardHeader`, `StatCard` - Content containers
- `Toast`, `ToastProvider` - Notification system
- `ErrorBoundary` - Error recovery UI
- `Switch`, `Badge`, `Alert` - UI primitives

**Layout Components (`src/components/layout/`):**
- `Layout` - Main page wrapper with sidebar
- `Header` - Top navigation bar
- `Sidebar` - Navigation menu

### API Client

Located in `src/api/endpoints.ts`. Uses Axios with WordPress nonce authentication.

```ts
// API calls include WordPress nonce automatically
const client = axios.create({
  baseURL: window.peanutConnect.apiUrl,
  headers: {
    'X-WP-Nonce': window.peanutConnect.nonce,
  },
});
```

## Testing

### PHP Tests

Run PHPUnit tests:
```bash
composer install
./vendor/bin/phpunit
```

**Test Files:**
- `AuthTest.php` - Authentication logic
- `HealthTest.php` - Health data collection
- `UpdatesTest.php` - Update detection
- `ApiTest.php` - API endpoints
- `RateLimiterTest.php` - Rate limiting

### Frontend Tests

Run Vitest tests:
```bash
cd frontend
npm install
npm run test
```

**Test Files:**
- `Button.test.tsx` - Button component
- `Card.test.tsx` - Card components
- `Toast.test.tsx` - Toast notifications
- `ErrorBoundary.test.tsx` - Error handling
- `Settings.test.tsx` - Settings page

## Building & Releasing

### Development
```bash
# Start frontend dev server
cd frontend && npm run dev

# Start WordPress with wp-env
npx wp-env start
```

### Production Build
```bash
# Build frontend assets
cd frontend && npm run build

# Package plugin
npm run package
```

### Release Process
```bash
# Bump version and create release
npm run release:patch  # or :minor or :major
```

## Security Considerations

1. **Authentication**: All manager endpoints require Bearer token
2. **Rate Limiting**: Prevents brute-force and DoS attacks
3. **Permissions**: Granular control over what manager can access
4. **Log Protection**: .htaccess blocks direct file access
5. **Nonce Verification**: WordPress nonces prevent CSRF
6. **Input Sanitization**: All user input is sanitized

## Extending

See [HOOKS.md](./HOOKS.md) for available WordPress hooks and filters.
