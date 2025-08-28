FROM node
RUN mkdir -p /app
WORKDIR /app
COPY . .
RUN npm install
RUN npm run build
CMD npm run start