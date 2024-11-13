FROM node:20 AS frontend-builder
WORKDIR /var/www/frontend
RUN apt-get update && apt-get install -y \
    vim \
    zip \
    unzip \
    curl

COPY ./frontend .
COPY --chown=node:node . /var/www

USER node

RUN npm install && npm run build

FROM nginx:latest
COPY --from=frontend-builder /var/www/frontend/dist /var/www/frontend/dist
COPY ./docker/nginx/conf.d/frontend.conf /etc/nginx/conf.d/default.conf
