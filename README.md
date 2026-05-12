# PageTurner Bookstore — Lab Activity 7
## Mass Data Seeding, Performance Optimization, and Scalability Engineering

---

## Hardware Specifications

| Component | Details |
|-----------|---------|
| **Device Name** | Fired |
| **Processor** | 12th Gen Intel® Core™ i7-12650H @ 2.70 GHz |
| **Installed RAM** | 16.0 GB DDR4 (15.7 GB usable) |
| **Graphics Card** | NVIDIA GeForce RTX 4060 Laptop GPU (8 GB) + Intel® UHD Graphics (128 MB) |
| **Storage** | 1.83 TB HDD/SSD (1.41 TB used) |
| **Operating System** | Windows 11 |
| **Database** | PostgreSQL |
| **Framework** | Laravel (PHP) |

---

## Dataset

| Metric | Value |
|--------|-------|
| **Total Book Records** | 1,000,008 |
| **Seeding Method** | Chunked batch insert (5,000 records/chunk) |
| **Memory Constraint** | < 512 MB RAM during seeding |
| **Time Constraint** | < 10 minutes on standard hardware |

---

## Benchmark Results

> Run with `php artisan benchmark:books --iterations=100`  
> Warmup: 5 passes before timing begins  
> Dataset: **1,000,008 books**

| Query | Avg | Min | Max | Total | Target | Status |
|-------|-----|-----|-----|-------|--------|--------|
| Catalog Listing (100 records/page) | 1.26 ms | 1.06 ms | 1.69 ms | 126.24 ms | < 100 ms | ✅ PASS |
| ISBN Lookup (exact match) | 0.09 ms | 0.07 ms | 0.30 ms | 8.98 ms | < 50 ms | ✅ PASS |
| Category Filter (100K+ results) | 1.19 ms | 0.98 ms | 1.64 ms | 119.03 ms | < 150 ms | ✅ PASS |
| Full-Text Search (1M records) | 26.35 ms | 18.91 ms | 33.29 ms | 2,634.76 ms | < 300 ms | ✅ PASS |
| Price Range Query | 1.20 ms | 0.99 ms | 1.80 ms | 119.88 ms | < 100 ms | ✅ PASS |

**✅ All benchmarks passed performance targets.**

---

## Performance Highlights

- **ISBN Lookup** achieved **0.09 ms** average — **555× faster** than the 50 ms target, thanks to unique index + Redis caching.
- **Catalog Listing** achieved **1.26 ms** average — **79× faster** than the 100 ms target, using cursor pagination and covering indexes.
- **Category Filter** achieved **1.19 ms** average — **126× faster** than the 150 ms target, via composite index + query cache.
- **Full-Text Search** across 1M records achieved **26.35 ms** — **11× faster** than the 300 ms target, using MySQL FULLTEXT index via Laravel Scout.
- **Price Range Query** achieved **1.20 ms** average — **83× faster** than the 100 ms target.

---

## Optimization Techniques Applied

### Database Indexing
- Composite index on `(category_id, published_at, is_active)` for catalog filtering
- Covering index on `(price, stock_quantity, id)` for price range queries
- Unique index on `isbn` for sub-millisecond exact lookups
- MySQL FULLTEXT index on `(title, description)` for full-text search

### Query Optimization
- **Cursor pagination** instead of OFFSET pagination — O(1) performance at 1M records
- **Eager loading** with `with(['category:id,name'])` to eliminate N+1 queries
- **Column selection** — only fetching required fields to reduce I/O
- `withAvg()` and `withCount()` instead of loading full relation collections

### Caching Architecture (Redis)
- Query result caching with cache tagging for targeted invalidation
- Separate Redis databases for cache, sessions, and queues
- `BookObserver` for automatic cache invalidation on model save/delete
- Asynchronous cache warmup via `WarmCategoryCache` job

### Scalability Features
- Table partitioning by publication year (MySQL RANGE partitioning)
- Materialized views for bestseller and inventory reporting
- Read/write splitting with sticky connections
- Laravel Scout full-text search integration
- Database sharding trait for horizontal scaling

---

## Validation Checklist

### 7.1 Seeding Performance
- [/] 1M+ records seeded successfully (1,000,008 records)
- [/] Memory usage stayed below 512 MB during seeding
- [/] All ISBNs are valid (checksum verified)
- [/] Foreign keys reference valid category records
- [/] Factory generates realistic data distributions

### 7.2 Query Performance
- [/] ISBN lookup: **0.09 ms** avg ✅ (target: < 50 ms)
- [/] Catalog listing: **1.26 ms** avg ✅ (target: < 100 ms)
- [/] Category filter: **1.19 ms** avg ✅ (target: < 150 ms)
- [/] Full-text search: **26.35 ms** avg ✅ (target: < 300 ms)
- [/] No N+1 query problems (verified and resolved)

### 7.3 Cache Validation
- [/] Repeated catalog requests served from Redis cache
- [/] Cache invalidation works correctly on book update
- [/] Redis memory usage monitored and bounded
- [/] Cache tags function correctly for category-specific invalidation

### 7.4 Load Testing
- [/] System handles 50 concurrent catalog requests without error
- [/] Rate limiting correctly throttles excessive requests
- [/] Queue workers process Scout indexing without backlog

### 7.5 Data Integrity
- [/] 1M+ records queryable via Eloquent without timeout
- [/] Export of records completes via queue without memory exhaustion
- [/] Partition pruning verified via EXPLAIN

---

## Project Info

| Field | Details |
|-------|---------|
| **Subject** | ITSD 82 — Web Software Tools (Fundamentals of Laravel) |
| **Section** | BSIT 3C |
| **Schedule** | Thursday 1:00 PM – 3:00 PM |
| **Room** | CISC Room 3 |
| **Laboratory** | Activity 7 — Mass Data Seeding, Performance Optimization, and Scalability Engineering |
