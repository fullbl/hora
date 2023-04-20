FROM node:lts-alpine AS npm

WORKDIR /app

COPY package*.json ./

RUN npm install

EXPOSE 5173
CMD [ "npm", "run", "dev" ]


FROM npm AS npm_prod
RUN npm install -g http-server

COPY . .
RUN npm run build

EXPOSE 8080
CMD [ "http-server", "dist" ]