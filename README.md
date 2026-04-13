# EventHub Project

EventHub is a REST API platform for managing events, registrations, and user interactions.  
It is designed as a scalable backend system using Laravel, Docker, MySQL, and Redis.

The project demonstrates clean architecture, API design, and containerized infrastructure setup.

## Prerequisites

- Docker and Docker Compose installed.

## Setup Instructions

### 1. Create Docker Network
All containers use a shared external network:

```bash
docker network create project_net
```

### 2. Start Infrastructure Services

#### Start MySQL
```bash
cd mysql
docker-compose up -d
cd ..
```

#### Start Redis
```bash
cd redis
docker-compose up -d
cd ..
```

### 3. Setup the Application (EventHub)

Go to the `eventHub` directory and prepare the environment:

```bash
cd eventHub
cp .env.example .env
```

**Configure `.env`:**
Update `.env` based on `.env.example`.

#### Start Application Containers
Build and start the PHP-FPM and Nginx containers:

```bash
cd .docker
docker-compose up -d --build
cd ..
```

### 4. Initialize the Application
Run these commands within the `events-api-fpm` container to set up the database:

```bash
docker exec -it events-api-fpm php artisan key:generate
docker exec -it events-api-fpm php artisan migrate --seed
docker exec -it events-api-fpm php artisan storage:link
```

### 5. (Optional) Start DNS Proxy Server
If you need local domain resolution:

```bash
cd docker-proxy-server && docker-compose up -d && cd ..
```

## Accessing the Application

- **API:** [http://localhost:8080](http://localhost:8080)
- **API Documentation:** [http://localhost:8080/api-doc/v1.html](http://localhost:8080/api-doc/v1.html)

## Useful Commands

- **Run Tests:** `docker exec -it events-api-fpm php artisan test`
- **Check Linting:** `docker exec -it events-api-fpm composer run lint`
- **Tinker:** `docker exec -it events-api-fpm php artisan tinker`
