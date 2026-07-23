# Archive MVP - Laravel Backend

An Archive.org-style MVP backend built with Laravel 11, PostgreSQL (Supabase), and Supabase Auth.

## Features

- 📦 **Items**: Upload and manage archived items with metadata
- 📚 **Collections**: Organize items into collections
- 🔐 **Supabase Auth**: JWT-based authentication using Supabase Auth
- 🐘 **PostgreSQL**: Database hosted on Supabase
- 🐳 **Docker**: Containerized for easy deployment on Render
- 🚀 **RESTful API**: Clean API endpoints with proper authorization

## Tech Stack

- **Framework**: Laravel 11
- **Database**: PostgreSQL (Supabase)
- **Auth**: Supabase JWT verification
- **Deployment**: Docker on Render

## Database Schema

### Users
- `id` (UUID, primary key)
- `email` (string, unique)
- `name` (string, nullable)
- `created_at`, `updated_at`

### Items
- `id` (bigint, primary key)
- `title` (string)
- `description` (text, nullable)
- `file_url` (string)
- `file_type` (string)
- `uploader_id` (UUID, foreign key to users)
- `created_at`, `updated_at`

### Collections
- `id` (bigint, primary key)
- `name` (string)
- `description` (text, nullable)
- `owner_id` (UUID, foreign key to users)
- `created_at`, `updated_at`

### Item_Collection (pivot table)
- `id` (bigint, primary key)
- `item_id` (bigint, foreign key)
- `collection_id` (bigint, foreign key)
- `created_at`, `updated_at`

## API Endpoints

### Public Endpoints

```
GET /api/items              - List all items (paginated)
GET /api/items/{id}         - Get single item with details
GET /api/collections        - List all collections (paginated)
GET /api/collections/{id}   - Get single collection with items
```

### Protected Endpoints (require Authorization header)

```
GET /api/me                                    - Get authenticated user

POST /api/items                                - Create new item
PUT /api/items/{id}                            - Update item (owner only)
DELETE /api/items/{id}                         - Delete item (owner only)

POST /api/collections                          - Create new collection
PUT /api/collections/{id}                      - Update collection (owner only)
DELETE /api/collections/{id}                   - Delete collection (owner only)

POST /api/collections/{id}/items               - Add item to collection
DELETE /api/collections/{id}/items/{itemId}    - Remove item from collection
```

### Authentication

Protected endpoints require a Supabase JWT token in the Authorization header:

```
Authorization: Bearer <supabase-jwt-token>
```

## Local Development Setup

### Prerequisites

- Docker and Docker Compose
- Supabase account
- PostgreSQL database on Supabase

### Setup Steps

1. **Clone the repository**

```bash
cd archive-backend
```

2. **Copy environment file**

```bash
cp .env.example .env
```

3. **Configure environment variables**

Edit `.env` and set:

```env
APP_KEY=base64:YOUR_APP_KEY_HERE

DB_CONNECTION=pgsql
DB_HOST=db.YOUR_PROJECT.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

SUPABASE_URL=https://YOUR_PROJECT.supabase.co
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_JWT_SECRET=your_jwt_secret
```

**Finding your Supabase credentials:**

- Go to your Supabase project dashboard
- Navigate to Settings > API
- Copy the JWT Secret (this is critical for auth)
- Copy the anon/public key
- Database credentials are in Settings > Database

4. **Build and run with Docker**

```bash
docker build -t archive-backend -f Dockerfile.simple .
docker run -p 8080:8080 --env-file .env archive-backend
```

Or without Docker (requires PHP 8.2+):

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan serve --port=8080
```

## Deployment to Render

### Step 1: Prepare Your Supabase Database

1. Create a Supabase project at [supabase.com](https://supabase.com)
2. Note your database connection details from Settings > Database
3. Note your JWT Secret from Settings > API

### Step 2: Create a New Web Service on Render

1. Go to [render.com](https://render.com) and sign in
2. Click **New +** > **Web Service**
3. Connect your GitHub repository
4. Configure the service:

**Basic Settings:**
- **Name**: `archive-mvp-backend` (or your choice)
- **Region**: Choose closest to your users
- **Branch**: `main` (or your branch)
- **Root Directory**: `archive-backend`
- **Runtime**: `Docker`
- **Instance Type**: Free or Starter

**Build Settings:**
- **Dockerfile Path**: `Dockerfile.simple`

**No build command needed** - Docker handles everything

### Step 3: Configure Environment Variables

In Render's Environment section, add these variables:

```
APP_NAME=Archive MVP
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_THIS_LOCALLY_WITH_php_artisan_key:generate
APP_URL=https://your-app.onrender.com

DB_CONNECTION=pgsql
DB_HOST=db.YOUR_PROJECT.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

SUPABASE_URL=https://YOUR_PROJECT.supabase.co
SUPABASE_ANON_KEY=your_anon_key
SUPABASE_JWT_SECRET=your_jwt_secret

SESSION_DRIVER=file
CACHE_DRIVER=file
```

**Important Notes:**
- Generate `APP_KEY` locally with `php artisan key:generate` and copy the value
- Or use any base64-encoded 32-character random string
- The JWT_SECRET is critical - get it from Supabase Settings > API > JWT Secret

### Step 4: Deploy

1. Click **Create Web Service**
2. Render will build and deploy your container
3. The start script automatically runs migrations on each deploy
4. Your API will be available at `https://your-app.onrender.com/api`

### Step 5: Verify Deployment

Test your API:

```bash
curl https://your-app.onrender.com/api/items
```

## Testing Authentication

### 1. Get a Supabase JWT Token

In your frontend or using Supabase client:

```javascript
const { data, error } = await supabase.auth.signUp({
  email: 'user@example.com',
  password: 'your-password'
})

const token = data.session.access_token
```

### 2. Make Authenticated Requests

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-app.onrender.com/api/me
```

### 3. Create an Item

```bash
curl -X POST \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Historic Document",
    "description": "An important document",
    "file_url": "https://example.com/file.pdf",
    "file_type": "application/pdf"
  }' \
  https://your-app.onrender.com/api/items
```

## Architecture Notes

### Supabase Auth Integration

This backend **does not** implement its own authentication. Instead:

1. Users authenticate with Supabase (handled in your frontend)
2. Supabase issues a JWT token
3. Frontend sends this token in the Authorization header
4. Laravel middleware (`VerifySupabaseJWT`) verifies the token using your JWT secret
5. User is automatically created/synced in local database on first request

### Why This Approach?

- ✅ No need to manage passwords or auth logic
- ✅ Supabase handles email verification, password resets, OAuth, etc.
- ✅ Frontend and backend share the same user identity
- ✅ Laravel only needs to verify tokens, not manage sessions

## Troubleshooting

### Issue: "Invalid or expired token"

**Cause**: JWT secret mismatch or token from wrong Supabase project

**Fix**: 
- Verify `SUPABASE_JWT_SECRET` matches your Supabase project (Settings > API > JWT Secret)
- Ensure token is fresh (tokens expire after 1 hour by default)

### Issue: Database connection failed

**Cause**: Wrong database credentials or Supabase not allowing connections

**Fix**:
- Check database credentials in Supabase Settings > Database
- Supabase databases accept connections from anywhere by default
- Verify SSL mode is set to 'prefer' in config/database.php

### Issue: Migrations fail on deploy

**Cause**: Database not reachable or migrations already ran

**Fix**:
- Check Render logs for specific error
- Verify DATABASE_URL or DB_* env vars are correct
- Migrations are safe to re-run (Laravel tracks which ran)

### Issue: 500 errors on all routes

**Cause**: Missing APP_KEY or misconfiguration

**Fix**:
- Ensure APP_KEY is set in environment variables
- Check Render logs for specific error
- Enable APP_DEBUG=true temporarily to see detailed errors

## Project Structure

```
archive-backend/
├── app/
│   ├── Http/
│   │   └── Middleware/
│   │       └── VerifySupabaseJWT.php    # JWT verification middleware
│   └── Models/
│       ├── User.php                      # User model
│       ├── Item.php                      # Item model
│       └── Collection.php                # Collection model
├── bootstrap/
│   └── app.php                           # Application bootstrap
├── config/
│   └── database.php                      # Database configuration
├── database/
│   └── migrations/                       # Database migrations
├── docker/
│   ├── nginx.conf                        # Nginx config (if using nginx)
│   ├── supervisord.conf                  # Supervisor config (if using nginx)
│   └── start.sh                          # Startup script
├── public/
│   └── index.php                         # Application entry point
├── routes/
│   ├── api.php                           # API routes
│   ├── web.php                           # Web routes
│   └── console.php                       # Console routes
├── storage/                              # Storage directories
├── .env.example                          # Environment template
├── composer.json                         # PHP dependencies
├── Dockerfile                            # Production Dockerfile (PHP-FPM + Nginx)
├── Dockerfile.simple                     # Simple Dockerfile (Laravel serve)
└── README.md                             # This file
```

## Contributing

This is an MVP. Future improvements could include:

- File upload handling (S3/Supabase Storage integration)
- Search functionality
- Tags and metadata
- User profiles
- Rate limiting
- Caching layer
- Full test suite

## License

MIT
