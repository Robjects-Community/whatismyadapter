# syntax=docker/dockerfile:1

FROM golang:1.24-alpine AS builder

# Set working directory for build
WORKDIR /build

# Copy go mod files
COPY container_src/go.mod container_src/go.sum ./

# Download dependencies
RUN go mod download

# Copy source code
COPY container_src/ ./

# Build static binary
RUN CGO_ENABLED=0 GOOS=linux go build -a -installsuffix cgo -o main .

# Runtime stage - use scratch for minimal image
FROM scratch

WORKDIR /app

# Copy CA certificates for HTTPS/TLS connections
COPY --from=builder /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/

# Copy the binary
COPY --from=builder /build/main .

# Expose port
EXPOSE 8080

# Run the application
CMD ["./main"]
