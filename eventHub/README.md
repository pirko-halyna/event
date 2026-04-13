# EventHub

EventHub is a platform for searching and visiting events.
It allows users to search for events and join them,
see details about event and its organizer and manage tickets to events.

## Local deployment
- Clone repository `git clone https://gitlab.com/halynka2000/eventhub.git`
- Go to project directory `cd eventhub`
- Install dependencies `composer install`
- Create environment file `cp .env.example .env`
- Generate app key `php artisan key:generate`
- Fill environments for database connection
- Run migrations `php artisan migrate`
- Run seeders `php artisan db:seed`
- Link storage `php artisan storage:link`
- Start docker container - `cd .docker && docker-compose up -d`

## Useful commands
- `php composer run lint` - check code styles correspond to PSR12
- `php composer run test` - run tests

## API documentation
Open API documentation is available at `/api-doc/v1.html` route.
