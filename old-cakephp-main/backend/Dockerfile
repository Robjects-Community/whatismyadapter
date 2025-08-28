FROM node:20-alpine

# Set the working directory
WORKDIR /backend
# Set environment variable to avoid warnings
ENV NODE_ENV=production
# Set the timezone to Asia/Shanghai
ENV TZ=America/Chicago
# Set the locale to UTF-8
ENV LANG=C.UTF-8
# Install necessary packages

# Copy package.json and package-lock.json
COPY package*.json ./

# Install dependencies
RUN npm install --production

# Copy the rest of the application code
COPY . .

# Expose the application port
EXPOSE 3000

# Start the application
CMD ["node", "server.js"]
# Note: Adjust the CMD line to match your application's entry point if it's different
# If you have a specific start script in package.json, you can use:
# CMD ["npm", "start"]
# or
# CMD ["node", "your-entry-file.js"]