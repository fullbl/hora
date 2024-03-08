FROM node:lts-alpine as build-stage

ARG LOCATION
ARG SECOND_LEVEL_DOMAIN
ARG SENTRY_DSN

WORKDIR /app
COPY --link package*.json ./
RUN npm install
COPY . .
RUN echo "${LOCATION}"
RUN npm run build

FROM nginx:stable-alpine as production-stage
COPY --from=build-stage /app/dist /usr/share/nginx/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]