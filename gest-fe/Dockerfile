FROM node:lts-alpine AS npm

WORKDIR /app

COPY package*.json ./

RUN npm install

EXPOSE 5173
CMD [ "npm", "run", "dev" ]
