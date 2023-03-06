# syntax=docker/dockerfile:1
FROM node:19.4.0-alpine as js-builder

WORKDIR /mqtt-datasource
RUN apk --no-cache add mage --repository=http://dl-cdn.alpinelinux.org/alpine/edge/testing && \
 wget -qO - https://github.com/grafana/mqtt-datasource/archive/refs/heads/main.zip | unzip -
RUN cd mqtt-datasource-main && yarn install --ignore-engines && NODE_OPTIONS=--openssl-legacy-provider yarn build

FROM grafana/grafana:9.3.2
COPY --from=js-builder /mqtt-datasource/mqtt-datasource-main/dist /var/lib/grafana/plugins