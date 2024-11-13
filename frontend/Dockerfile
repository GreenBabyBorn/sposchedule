FROM node:20

WORKDIR /var/www/frontend


# Install dependencies
RUN apt-get update && apt-get install -y \
    vim \
    zip \
    unzip \
    curl

COPY . /var/www/frontend/
COPY --chown=node:node . /var/www

# Change current user
USER node

CMD ["sh", "-c", "npm install &&  npm run build"]
