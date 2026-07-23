# API Documentation

## Overview

This document describes the REST API endpoints for the Archive MVP backend. All endpoints return JSON responses using Laravel API Resources for consistent formatting.

## Base URL

```
https://your-app.onrender.com/api
```

## Authentication

Protected endpoints require a Supabase JWT token in the Authorization header:

```
Authorization: Bearer <supabase-jwt-token>
```

---

## Items

### GET /items

List all items with pagination, filtering, and search.

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search items by title (case-insensitive) |
| `category` | string | Filter items by exact category match |
| `tag` | string | Filter items that have this tag |
| `per_page` | integer | Items per page (default: 20) |
| `page` | integer | Page number (default: 1) |

**Example Requests:**

```bash
# Get all items
GET /api/items

# Search by title
GET /api/items?search=historic

# Filter by category
GET /api/items?category=documents

# Filter by tag
GET /api/items?tag=public-domain

# Combine filters
GET /api/items?search=war&category=photos&per_page=50

# Pagination
GET /api/items?page=2&per_page=10
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "title": "Historic Document",
      "description": "An important historical document",
      "file_url": "https://example.supabase.co/storage/v1/object/public/files/doc.pdf",
      "file_type": "application/pdf",
      "category": "documents",
      "tags": ["history", "public-domain"],
      "uploader": {
        "id": "uuid-here",
        "email": "user@example.com",
        "name": "John Doe",
        "created_at": "2024-01-01T00:00:00.000000Z"
      },
      "collections": [],
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ],
  "meta": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  },
  "links": {
    "first": "https://your-app.onrender.com/api/items?page=1",
    "last": "https://your-app.onrender.com/api/items?page=5",
    "prev": null,
    "next": "https://your-app.onrender.com/api/items?page=2"
  }
}
```

### GET /items/{id}

Get a single item by ID with related data.

**Example Request:**

```bash
GET /api/items/1
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "title": "Historic Document",
    "description": "An important historical document",
    "file_url": "https://example.supabase.co/storage/v1/object/public/files/doc.pdf",
    "file_type": "application/pdf",
    "category": "documents",
    "tags": ["history", "public-domain"],
    "uploader": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "collections": [
      {
        "id": 5,
        "name": "Historical Archives",
        "description": "Important historical documents",
        "owner": {
          "id": "uuid-here",
          "email": "user@example.com",
          "name": "John Doe",
          "created_at": "2024-01-01T00:00:00.000000Z"
        },
        "created_at": "2024-01-10T00:00:00.000000Z",
        "updated_at": "2024-01-10T00:00:00.000000Z"
      }
    ],
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T10:30:00.000000Z"
  }
}
```

### POST /items 🔒

Create a new item. **Requires authentication.**

**Note:** This endpoint expects that the file has already been uploaded to Supabase Storage. Pass the resulting `file_url` from Supabase Storage along with metadata.

**Request Body:**

```json
{
  "title": "Historic Photo",
  "description": "A photo from 1945",
  "file_url": "https://example.supabase.co/storage/v1/object/public/files/photo.jpg",
  "file_type": "image/jpeg",
  "category": "photos",
  "tags": ["ww2", "history", "black-and-white"]
}
```

**Validation Rules:**

| Field | Rules |
|-------|-------|
| `title` | required, string, max:255 |
| `description` | nullable, string, max:5000 |
| `file_url` | required, valid URL, max:1000 |
| `file_type` | required, string, max:100 |
| `category` | nullable, string, max:100 |
| `tags` | nullable, array |
| `tags.*` | string, max:50 |

**Example Request:**

```bash
curl -X POST https://your-app.onrender.com/api/items \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Historic Photo",
    "description": "A photo from 1945",
    "file_url": "https://example.supabase.co/storage/v1/object/public/files/photo.jpg",
    "file_type": "image/jpeg",
    "category": "photos",
    "tags": ["ww2", "history"]
  }'
```

**Response (201 Created):**

```json
{
  "data": {
    "id": 2,
    "title": "Historic Photo",
    "description": "A photo from 1945",
    "file_url": "https://example.supabase.co/storage/v1/object/public/files/photo.jpg",
    "file_type": "image/jpeg",
    "category": "photos",
    "tags": ["ww2", "history"],
    "uploader": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "collections": [],
    "created_at": "2024-01-20T15:45:00.000000Z",
    "updated_at": "2024-01-20T15:45:00.000000Z"
  }
}
```

### PUT /items/{id} 🔒

Update an item. **Requires authentication. Owner only.**

**Request Body (all fields optional):**

```json
{
  "title": "Updated Title",
  "description": "Updated description",
  "category": "new-category",
  "tags": ["new", "tags"]
}
```

**Example Request:**

```bash
curl -X PUT https://your-app.onrender.com/api/items/2 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Historic Photo",
    "tags": ["ww2", "history", "vintage"]
  }'
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 2,
    "title": "Updated Historic Photo",
    "description": "A photo from 1945",
    "file_url": "https://example.supabase.co/storage/v1/object/public/files/photo.jpg",
    "file_type": "image/jpeg",
    "category": "photos",
    "tags": ["ww2", "history", "vintage"],
    "uploader": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "collections": [],
    "created_at": "2024-01-20T15:45:00.000000Z",
    "updated_at": "2024-01-20T16:00:00.000000Z"
  }
}
```

### DELETE /items/{id} 🔒

Delete an item. **Requires authentication. Owner only.**

**Example Request:**

```bash
curl -X DELETE https://your-app.onrender.com/api/items/2 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**

```json
{
  "message": "Item deleted successfully"
}
```

---

## Collections

### GET /collections

List all collections with pagination and search.

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `search` | string | Search collections by name (case-insensitive) |
| `per_page` | integer | Collections per page (default: 20) |
| `page` | integer | Page number (default: 1) |

**Example Requests:**

```bash
# Get all collections
GET /api/collections

# Search by name
GET /api/collections?search=historical

# Pagination
GET /api/collections?page=2&per_page=10
```

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "name": "Historical Archives",
      "description": "Important historical documents",
      "owner": {
        "id": "uuid-here",
        "email": "user@example.com",
        "name": "John Doe",
        "created_at": "2024-01-01T00:00:00.000000Z"
      },
      "created_at": "2024-01-10T00:00:00.000000Z",
      "updated_at": "2024-01-10T00:00:00.000000Z"
    }
  ],
  "meta": {
    "total": 25,
    "per_page": 20,
    "current_page": 1,
    "last_page": 2
  },
  "links": {
    "first": "https://your-app.onrender.com/api/collections?page=1",
    "last": "https://your-app.onrender.com/api/collections?page=2",
    "prev": null,
    "next": "https://your-app.onrender.com/api/collections?page=2"
  }
}
```

### GET /collections/{id}

Get a single collection by ID with all its items.

**Example Request:**

```bash
GET /api/collections/1
```

**Response:**

```json
{
  "data": {
    "id": 1,
    "name": "Historical Archives",
    "description": "Important historical documents",
    "owner": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "items": [
      {
        "id": 1,
        "title": "Historic Document",
        "description": "An important historical document",
        "file_url": "https://example.supabase.co/storage/v1/object/public/files/doc.pdf",
        "file_type": "application/pdf",
        "category": "documents",
        "tags": ["history", "public-domain"],
        "uploader": {
          "id": "uuid-here",
          "email": "user@example.com",
          "name": "John Doe",
          "created_at": "2024-01-01T00:00:00.000000Z"
        },
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z"
      }
    ],
    "items_count": 1,
    "created_at": "2024-01-10T00:00:00.000000Z",
    "updated_at": "2024-01-10T00:00:00.000000Z"
  }
}
```

### POST /collections 🔒

Create a new collection. **Requires authentication.**

**Request Body:**

```json
{
  "name": "My Collection",
  "description": "A collection of interesting items"
}
```

**Validation Rules:**

| Field | Rules |
|-------|-------|
| `name` | required, string, max:255 |
| `description` | nullable, string, max:5000 |

**Example Request:**

```bash
curl -X POST https://your-app.onrender.com/api/collections \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "World War II Photos",
    "description": "A curated collection of WW2 photographs"
  }'
```

**Response (201 Created):**

```json
{
  "data": {
    "id": 5,
    "name": "World War II Photos",
    "description": "A curated collection of WW2 photographs",
    "owner": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "created_at": "2024-01-20T16:30:00.000000Z",
    "updated_at": "2024-01-20T16:30:00.000000Z"
  }
}
```

### PUT /collections/{id} 🔒

Update a collection. **Requires authentication. Owner only.**

**Request Body (all fields optional):**

```json
{
  "name": "Updated Collection Name",
  "description": "Updated description"
}
```

**Example Request:**

```bash
curl -X PUT https://your-app.onrender.com/api/collections/5 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "An expanded collection of WW2 photographs and documents"
  }'
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 5,
    "name": "World War II Photos",
    "description": "An expanded collection of WW2 photographs and documents",
    "owner": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "created_at": "2024-01-20T16:30:00.000000Z",
    "updated_at": "2024-01-20T16:45:00.000000Z"
  }
}
```

### DELETE /collections/{id} 🔒

Delete a collection. **Requires authentication. Owner only.**

**Example Request:**

```bash
curl -X DELETE https://your-app.onrender.com/api/collections/5 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**

```json
{
  "message": "Collection deleted successfully"
}
```

### POST /collections/{id}/items 🔒

Add an item to a collection. **Requires authentication. Owner only.**

**Request Body:**

```json
{
  "item_id": 2
}
```

**Validation Rules:**

| Field | Rules |
|-------|-------|
| `item_id` | required, exists in items table |

**Example Request:**

```bash
curl -X POST https://your-app.onrender.com/api/collections/5/items \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "item_id": 2
  }'
```

**Response (200 OK):**

```json
{
  "data": {
    "id": 5,
    "name": "World War II Photos",
    "description": "A curated collection of WW2 photographs",
    "owner": {
      "id": "uuid-here",
      "email": "user@example.com",
      "name": "John Doe",
      "created_at": "2024-01-01T00:00:00.000000Z"
    },
    "items": [
      {
        "id": 2,
        "title": "Historic Photo",
        "description": "A photo from 1945",
        "file_url": "https://example.supabase.co/storage/v1/object/public/files/photo.jpg",
        "file_type": "image/jpeg",
        "category": "photos",
        "tags": ["ww2", "history"],
        "uploader": {
          "id": "uuid-here",
          "email": "user@example.com",
          "name": "John Doe",
          "created_at": "2024-01-01T00:00:00.000000Z"
        },
        "created_at": "2024-01-20T15:45:00.000000Z",
        "updated_at": "2024-01-20T15:45:00.000000Z"
      }
    ],
    "items_count": 1,
    "created_at": "2024-01-20T16:30:00.000000Z",
    "updated_at": "2024-01-20T16:30:00.000000Z"
  }
}
```

### DELETE /collections/{id}/items/{itemId} 🔒

Remove an item from a collection. **Requires authentication. Owner only.**

**Example Request:**

```bash
curl -X DELETE https://your-app.onrender.com/api/collections/5/items/2 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**

```json
{
  "message": "Item removed from collection successfully"
}
```

---

## User

### GET /me 🔒

Get the authenticated user's profile. **Requires authentication.**

**Example Request:**

```bash
curl https://your-app.onrender.com/api/me \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response (200 OK):**

```json
{
  "data": {
    "id": "uuid-here",
    "email": "user@example.com",
    "name": "John Doe",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

---

## Error Responses

### Validation Error (422)

```json
{
  "message": "The title field is required. (and 1 more error)",
  "errors": {
    "title": [
      "The title field is required."
    ],
    "file_url": [
      "The file url field must be a valid URL."
    ]
  }
}
```

### Unauthorized (401)

```json
{
  "error": "Unauthorized"
}
```

Or:

```json
{
  "error": "Invalid or expired token"
}
```

### Forbidden (403)

```json
{
  "message": "Forbidden"
}
```

### Not Found (404)

```json
{
  "message": "No query results for model [App\\Models\\Item] 123"
}
```

---

## Workflow Example: Upload and Archive a File

Here's a complete workflow for uploading a file to Supabase Storage and creating an item in the archive:

### Step 1: Upload file to Supabase Storage (Frontend)

```javascript
// In your frontend (Next.js, React, etc.)
const { data: uploadData, error: uploadError } = await supabase.storage
  .from('files')
  .upload(`public/${Date.now()}-${file.name}`, file)

if (uploadError) {
  console.error('Upload failed:', uploadError)
  return
}

// Get the public URL
const { data: { publicUrl } } = supabase.storage
  .from('files')
  .getPublicUrl(uploadData.path)
```

### Step 2: Get Supabase JWT token

```javascript
const { data: { session } } = await supabase.auth.getSession()
const token = session.access_token
```

### Step 3: Create item metadata in archive

```javascript
const response = await fetch('https://your-app.onrender.com/api/items', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    title: file.name,
    description: 'User uploaded document',
    file_url: publicUrl,
    file_type: file.type,
    category: 'documents',
    tags: ['user-upload', '2024']
  })
})

const item = await response.json()
console.log('Item created:', item)
```

### Step 4 (Optional): Add to a collection

```javascript
await fetch(`https://your-app.onrender.com/api/collections/${collectionId}/items`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    item_id: item.data.id
  })
})
```

---

## Notes

- All timestamps are in ISO 8601 format with UTC timezone
- Tags are stored as JSON arrays and can be filtered individually
- Pagination uses Laravel's standard pagination format
- Search is case-insensitive using PostgreSQL's `ilike` operator
- The `file_url` should point to an already-uploaded file in Supabase Storage
- Owner authorization is enforced on update/delete operations
- Users are automatically created/synced from Supabase JWT on first authenticated request
