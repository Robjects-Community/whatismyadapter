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
# For example, if your main file is `app.js`, you would use:
# CMD ["node", "app.js"]
# If you are using a framework like Express, you might have a different entry point.
# If you are using a process manager like PM2, you might use:
# CMD ["pm2-runtime", "start", "server.js"]
# If you are using a different port, make sure to expose that port and set the environment variable accordingly.        
# If you have a specific start script in package.json, you can use:
# CMD ["npm", "start"]
# or
# CMD ["node", "your-entry-file.js"]