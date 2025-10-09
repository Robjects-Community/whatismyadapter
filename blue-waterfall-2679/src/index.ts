import { Container, getContainer } from "@cloudflare/containers";
import { Hono } from "hono";

/**
 * WillowCMS Container Class
 * Manages Go container instances with MySQL and Redis connectivity
 */
export class MyContainer extends Container<Env> {
  // Port the container listens on
  defaultPort = 8080;
  
  // Time before container sleeps due to inactivity
  sleepAfter = "5m";
  
  // Environment variables passed to the container
  // These are automatically injected from wrangler.jsonc vars and secrets
  envVars = {
    DB_HOST: this.env.DB_HOST,
    DB_DATABASE: this.env.DB_DATABASE,
    DB_USERNAME: this.env.DB_USERNAME,
    DB_PASSWORD: this.env.DB_PASSWORD,
    DB_PORT: this.env.DB_PORT,
    REDIS_HOST: this.env.REDIS_HOST,
    REDIS_PORT: this.env.REDIS_PORT,
    REDIS_USERNAME: this.env.REDIS_USERNAME,
    REDIS_PASSWORD: this.env.REDIS_PASSWORD,
  };

  // Optional lifecycle hooks
  override onStart() {
    console.log("[Container] WillowCMS container started");
  }

  override onStop() {
    console.log("[Container] WillowCMS container stopped");
  }

  override onError(error: unknown) {
    console.error("[Container] Error:", error);
  }
}

// Create Hono app with proper typing for Cloudflare Workers
const app = new Hono<{
  Bindings: Env;
}>();

// Home route with available endpoints
app.get("/", (c) => {
  return c.json({
    service: "WillowCMS Container Service",
    version: "1.0.0",
    endpoints: {
      health: "GET /health - Check MySQL and Redis connectivity",
      articles: "GET /articles - Fetch all published articles",
      article: "GET /articles/:id - Fetch specific article by ID",
      cache_get: "GET /cache/:key - Get value from Redis cache",
      cache_set: "POST /cache - Set key-value in Redis (body: {key, value, ttl})",
    },
  });
});

// Health check endpoint - routes to container /health
app.get("/health", async (c) => {
  const container = getContainer(c.env.MY_CONTAINER, "health-check");
  return await container.fetch(c.req.raw);
});

// Get all published articles - routes to container /articles
app.get("/articles", async (c) => {
  const container = getContainer(c.env.MY_CONTAINER, "articles");
  return await container.fetch(c.req.raw);
});

// Get specific article by ID - routes to container /articles/:id
app.get("/articles/:id", async (c) => {
  const container = getContainer(c.env.MY_CONTAINER, "articles");
  return await container.fetch(c.req.raw);
});

// Get value from Redis cache - routes to container /cache/:key
app.get("/cache/:key", async (c) => {
  const container = getContainer(c.env.MY_CONTAINER, "cache");
  return await container.fetch(c.req.raw);
});

// Set value in Redis cache - routes to container POST /cache
app.post("/cache", async (c) => {
  const container = getContainer(c.env.MY_CONTAINER, "cache");
  return await container.fetch(c.req.raw);
});

export default app;
