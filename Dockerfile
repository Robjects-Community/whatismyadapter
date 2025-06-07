# We don't want to start from scratch.
# That is why we tell node here to use the current node image as base.
FROM node:alpine3.11

# Create an application directory
RUN mkdir -p /app

# The /app directory should act as the main application directory
WORKDIR /app

# Copy the app package and package-lock.json file
COPY frontend/package*.json ./

# Install node packages
RUN npm install

# Copy or project directory (locally) in the current directory of our docker image (/app)
COPY frontend/ .

# Build the app
RUN npm run build

# # Expose $PORT on container.
# # We use a varibale here as the port is something that can differ on the environment.
# EXPOSE $PORT

# # Set host to localhost / the docker image
# ENV NUXT_HOST=0.0.0.0

# # Set app port
# ENV NUXT_PORT=$PORT

# # Set the base url
# ENV PROXY_API=$PROXY_API

# # Set the browser base url
# ENV PROXY_LOGIN=$PROXY_LOGIN

# Expose the application port
EXPOSE 3000
# Set environment variable to avoid warnings
ENV NODE_ENV=development
# Set the timezone to America/Chicago
ENV TZ=America/Chicago
# Set the locale to UTF-8
ENV LANG=C.UTF-8
# Set the host to
ENV NUXT_HOST=0.0.0.0
# Set the app port
ENV NUXT_PORT=3000
# Set the base URL for the API
ENV PROXY_API=http://localhost:3000/api
# Set the browser base URL
ENV PROXY_LOGIN=http://localhost:3000/login
# Set the base URL for the frontend
ENV PROXY_FRONTEND=http://localhost:3000
# Set the base URL for the backend
ENV PROXY_BACKEND=http://localhost:3000


# Start the app
CMD [ "npm", "start" ]