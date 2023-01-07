# syntax=docker/dockerfile:1
FROM node:lts-alpine as js-builder

WORKDIR /mqtt-datasource
RUN apk --no-cache add mage --repository=http://dl-cdn.alpinelinux.org/alpine/edge/testing
RUN wget -qO - https://github.com/grafana/mqtt-datasource/archive/refs/heads/main.zip | unzip -
RUN cd mqtt-datasource-main && yarn install && NODE_OPTIONS=--openssl-legacy-provider yarn build

FROM grafana/grafana:latest
COPY --from=js-builder /mqtt-datasource/mqtt-datasource-main/dist /var/lib/grafana/plugins