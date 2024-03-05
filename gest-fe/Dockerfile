FROM node:lts-alpine

WORKDIR /app

COPY --link package*.json ./

RUN npm install

EXPOSE 5173
CMD [ "npm", "run", "dev" ]
