package main

import (
	"context"
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"os/signal"
	"strings"
	"syscall"
	"time"

	_ "github.com/go-sql-driver/mysql"
	"github.com/redis/go-redis/v9"
)

// Global database connections
var (
	db  *sql.DB
	rdb *redis.Client
)

// Article represents a WillowCMS article
type Article struct {
	ID          string     `json:"id"`
	UserID      string     `json:"user_id,omitempty"`
	Title       string     `json:"title"`
	Slug        string     `json:"slug"`
	Body        *string    `json:"body,omitempty"`
	Markdown    *string    `json:"markdown,omitempty"`
	IsPublished bool       `json:"is_published"`
	Created     *time.Time `json:"created,omitempty"`
	Modified    *time.Time `json:"modified,omitempty"`
}

// CacheRequest represents a cache set request
type CacheRequest struct {
	Key   string `json:"key"`
	Value string `json:"value"`
	TTL   int    `json:"ttl"` // TTL in seconds
}

// HealthResponse represents the health check response
type HealthResponse struct {
	MySQL string `json:"mysql"`
	Redis string `json:"redis"`
}

// initMySQL initializes MySQL connection with retry logic
func initMySQL() (*sql.DB, error) {
	dbHost := getEnv("DB_HOST", "mysql")
	dbPort := getEnv("DB_PORT", "3306")
	dbDatabase := getEnv("DB_DATABASE", "cms")
	dbUsername := getEnv("DB_USERNAME", "cms_user")
	dbPassword := getEnv("DB_PASSWORD", "password")

	// Build DSN connection string
	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true&timeout=10s",
		dbUsername, dbPassword, dbHost, dbPort, dbDatabase)

	log.Printf("[MySQL] Connecting to %s:%s/%s as %s", dbHost, dbPort, dbDatabase, dbUsername)

	var database *sql.DB
	var err error

	// Retry logic: 3 attempts with exponential backoff
	for i := 0; i < 3; i++ {
		database, err = sql.Open("mysql", dsn)
		if err != nil {
			log.Printf("[MySQL] Connection attempt %d failed: %v", i+1, err)
			time.Sleep(time.Duration(i+1) * 2 * time.Second)
			continue
		}

		// Configure connection pool
		database.SetMaxOpenConns(25)
		database.SetMaxIdleConns(5)
		database.SetConnMaxLifetime(5 * time.Minute)

		// Test the connection
		ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
		defer cancel()

		err = database.PingContext(ctx)
		if err != nil {
			log.Printf("[MySQL] Ping attempt %d failed: %v", i+1, err)
			database.Close()
			time.Sleep(time.Duration(i+1) * 2 * time.Second)
			continue
		}

		log.Printf("[MySQL] Successfully connected to database")
		return database, nil
	}

	return nil, fmt.Errorf("failed to connect to MySQL after 3 attempts: %w", err)
}

// initRedis initializes Redis connection with retry logic
func initRedis() (*redis.Client, error) {
	redisHost := getEnv("REDIS_HOST", "redis")
	redisPort := getEnv("REDIS_PORT", "6379")
	redisPassword := getEnv("REDIS_PASSWORD", "")
	redisUsername := getEnv("REDIS_USERNAME", "")

	log.Printf("[Redis] Connecting to %s:%s", redisHost, redisPort)

	client := redis.NewClient(&redis.Options{
		Addr:         fmt.Sprintf("%s:%s", redisHost, redisPort),
		Username:     redisUsername,
		Password:     redisPassword,
		DB:           0,
		DialTimeout:  10 * time.Second,
		ReadTimeout:  5 * time.Second,
		WriteTimeout: 5 * time.Second,
		PoolSize:     10,
	})

	// Retry logic: 3 attempts with exponential backoff
	for i := 0; i < 3; i++ {
		ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
		defer cancel()

		_, err := client.Ping(ctx).Result()
		if err != nil {
			log.Printf("[Redis] Connection attempt %d failed: %v", i+1, err)
			time.Sleep(time.Duration(i+1) * 2 * time.Second)
			continue
		}

		log.Printf("[Redis] Successfully connected")
		return client, nil
	}

	return nil, fmt.Errorf("failed to connect to Redis after 3 attempts")
}

// getEnv gets environment variable with fallback default
func getEnv(key, defaultValue string) string {
	if value := os.Getenv(key); value != "" {
		return value
	}
	return defaultValue
}

// respondJSON sends a JSON response
func respondJSON(w http.ResponseWriter, status int, data interface{}) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	if err := json.NewEncoder(w).Encode(data); err != nil {
		log.Printf("[Error] Failed to encode JSON response: %v", err)
	}
}

// respondError sends an error response
func respondError(w http.ResponseWriter, status int, message string) {
	respondJSON(w, status, map[string]string{"error": message})
}

// securityHeaders middleware adds security headers
func securityHeaders(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("X-Content-Type-Options", "nosniff")
		w.Header().Set("X-Frame-Options", "DENY")
		w.Header().Set("Content-Security-Policy", "default-src 'self'")
		next.ServeHTTP(w, r)
	})
}

// loggingMiddleware logs all HTTP requests
func loggingMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		start := time.Now()
		log.Printf("[Request] %s %s", r.Method, r.URL.Path)
		next.ServeHTTP(w, r)
		log.Printf("[Response] %s %s - %v", r.Method, r.URL.Path, time.Since(start))
	})
}

// recoveryMiddleware recovers from panics
func recoveryMiddleware(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		defer func() {
			if err := recover(); err != nil {
				log.Printf("[Panic] Recovered from panic: %v", err)
				respondError(w, http.StatusInternalServerError, "Internal server error")
			}
		}()
		next.ServeHTTP(w, r)
	})
}

// healthHandler handles health check endpoint
func healthHandler(w http.ResponseWriter, r *http.Request) {
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	response := HealthResponse{
		MySQL: "disconnected",
		Redis: "disconnected",
	}

	// Check MySQL connection
	if err := db.PingContext(ctx); err != nil {
		log.Printf("[Health] MySQL ping failed: %v", err)
	} else {
		response.MySQL = "connected"
	}

	// Check Redis connection
	if _, err := rdb.Ping(ctx).Result(); err != nil {
		log.Printf("[Health] Redis ping failed: %v", err)
	} else {
		response.Redis = "connected"
	}

	status := http.StatusOK
	if response.MySQL == "disconnected" || response.Redis == "disconnected" {
		status = http.StatusServiceUnavailable
	}

	respondJSON(w, status, response)
}

// articlesHandler fetches all published articles
func articlesHandler(w http.ResponseWriter, r *http.Request) {
	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	query := `SELECT id, title, slug, body, is_published, created 
	          FROM articles 
	          WHERE is_published = 1 
	          ORDER BY created DESC 
	          LIMIT 50`

	start := time.Now()
	rows, err := db.QueryContext(ctx, query)
	if err != nil {
		log.Printf("[Error] Failed to query articles: %v", err)
		respondError(w, http.StatusInternalServerError, "Database query failed")
		return
	}
	defer rows.Close()

	articles := []Article{}
	for rows.Next() {
		var article Article
		err := rows.Scan(&article.ID, &article.Title, &article.Slug, &article.Body, &article.IsPublished, &article.Created)
		if err != nil {
			log.Printf("[Error] Failed to scan article row: %v", err)
			continue
		}
		articles = append(articles, article)
	}

	duration := time.Since(start)
	log.Printf("[Query] Fetched %d articles in %v", len(articles), duration)

	respondJSON(w, http.StatusOK, articles)
}

// articleByIDHandler fetches a specific article by ID
func articleByIDHandler(w http.ResponseWriter, r *http.Request) {
	// Extract ID from URL path (e.g., /articles/123)
	pathParts := strings.Split(r.URL.Path, "/")
	if len(pathParts) < 3 {
		respondError(w, http.StatusBadRequest, "Invalid article ID")
		return
	}
	articleID := pathParts[2]

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	query := `SELECT id, user_id, title, slug, body, markdown, is_published, created, modified 
	          FROM articles 
	          WHERE id = ?`

	var article Article
	err := db.QueryRowContext(ctx, query, articleID).Scan(
		&article.ID,
		&article.UserID,
		&article.Title,
		&article.Slug,
		&article.Body,
		&article.Markdown,
		&article.IsPublished,
		&article.Created,
		&article.Modified,
	)

	if err == sql.ErrNoRows {
		respondError(w, http.StatusNotFound, "Article not found")
		return
	}

	if err != nil {
		log.Printf("[Error] Failed to query article %s: %v", articleID, err)
		respondError(w, http.StatusInternalServerError, "Database query failed")
		return
	}

	respondJSON(w, http.StatusOK, article)
}

// cacheGetHandler retrieves a value from Redis cache
func cacheGetHandler(w http.ResponseWriter, r *http.Request) {
	// Extract key from URL path (e.g., /cache/mykey)
	pathParts := strings.Split(r.URL.Path, "/")
	if len(pathParts) < 3 {
		respondError(w, http.StatusBadRequest, "Invalid cache key")
		return
	}
	key := pathParts[2]

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	value, err := rdb.Get(ctx, key).Result()
	if err == redis.Nil {
		respondError(w, http.StatusNotFound, "Key not found")
		return
	}

	if err != nil {
		log.Printf("[Error] Failed to get key %s from Redis: %v", key, err)
		respondError(w, http.StatusInternalServerError, "Cache operation failed")
		return
	}

	respondJSON(w, http.StatusOK, map[string]string{
		"key":   key,
		"value": value,
	})
}

// cacheSetHandler sets a key-value pair in Redis cache
func cacheSetHandler(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		respondError(w, http.StatusMethodNotAllowed, "Method not allowed")
		return
	}

	var req CacheRequest
	if err := json.NewDecoder(r.Body).Decode(&req); err != nil {
		respondError(w, http.StatusBadRequest, "Invalid JSON payload")
		return
	}

	if req.Key == "" || req.Value == "" {
		respondError(w, http.StatusBadRequest, "Key and value are required")
		return
	}

	ctx, cancel := context.WithTimeout(context.Background(), 5*time.Second)
	defer cancel()

	ttl := time.Duration(req.TTL) * time.Second
	if req.TTL == 0 {
		ttl = 0 // No expiration
	}

	err := rdb.Set(ctx, req.Key, req.Value, ttl).Err()
	if err != nil {
		log.Printf("[Error] Failed to set key %s in Redis: %v", req.Key, err)
		respondError(w, http.StatusInternalServerError, "Cache operation failed")
		return
	}

	respondJSON(w, http.StatusOK, map[string]string{
		"status":  "success",
		"message": fmt.Sprintf("Key '%s' set successfully", req.Key),
	})
}

// logConnectionStats periodically logs connection pool statistics
func logConnectionStats() {
	ticker := time.NewTicker(30 * time.Second)
	go func() {
		for range ticker.C {
			stats := db.Stats()
			log.Printf("[Stats] MySQL Pool - Open: %d, InUse: %d, Idle: %d, WaitCount: %d",
				stats.OpenConnections, stats.InUse, stats.Idle, stats.WaitCount)

			poolStats := rdb.PoolStats()
			log.Printf("[Stats] Redis Pool - Hits: %d, Misses: %d, Timeouts: %d",
				poolStats.Hits, poolStats.Misses, poolStats.Timeouts)
		}
	}()
}

func main() {
	log.Printf("[Server] Starting WillowCMS Container Service...")

	// Initialize MySQL connection
	var err error
	db, err = initMySQL()
	if err != nil {
		log.Fatalf("[Fatal] Failed to initialize MySQL: %v", err)
	}
	defer db.Close()

	// Initialize Redis connection
	rdb, err = initRedis()
	if err != nil {
		log.Fatalf("[Fatal] Failed to initialize Redis: %v", err)
	}
	defer rdb.Close()

	// Start connection pool monitoring
	logConnectionStats()

	// Set up HTTP router
	router := http.NewServeMux()
	router.HandleFunc("/health", healthHandler)
	router.HandleFunc("/articles", articlesHandler)
	router.HandleFunc("/articles/", articleByIDHandler)
	router.HandleFunc("/cache/", func(w http.ResponseWriter, r *http.Request) {
		if r.Method == http.MethodGet {
			cacheGetHandler(w, r)
		} else if r.Method == http.MethodPost {
			cacheSetHandler(w, r)
		} else {
			respondError(w, http.StatusMethodNotAllowed, "Method not allowed")
		}
	})
	router.HandleFunc("/cache", cacheSetHandler) // POST only

	// Apply middleware
	handler := recoveryMiddleware(loggingMiddleware(securityHeaders(router)))

	// Set up HTTP server
	server := &http.Server{
		Addr:    ":8080",
		Handler: handler,
	}

	// Listen for shutdown signals
	stop := make(chan os.Signal, 1)
	signal.Notify(stop, syscall.SIGINT, syscall.SIGTERM)

	// Start server in goroutine
	go func() {
		log.Printf("[Server] Listening on %s", server.Addr)
		if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			log.Fatalf("[Fatal] Server error: %v", err)
		}
	}()

	// Wait for shutdown signal
	sig := <-stop
	log.Printf("[Server] Received signal (%s), shutting down gracefully...", sig)

	// Graceful shutdown with timeout
	ctx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	if err := server.Shutdown(ctx); err != nil {
		log.Printf("[Error] Server shutdown error: %v", err)
	}

	log.Println("[Server] Shutdown complete")
}
