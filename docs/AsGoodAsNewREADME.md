# Deployment / Usage

## Installation
This project is hosted on GitHub and uses Docker as a development environment.
Please see original [README.md](../README.md) for information about the forked parent project.
- To start clone the repository from GitHub
  - ```git clone https://github.com/atodt/symfony-docker-shopping-cart```
  - cd into the project directory
- Make sure you have `Docker` installed on your computer. 
If not, download it from the official [Docker](https://www.docker.com) website.
- Follow the Getting Started section in the original [README.md](../README.md)
- For testing of the Docker installation go to [localhost](https://localhost) in your browser to see `Hello, Cart!`

The API implementation should be working at this point

## Usage
The best way to use the API manually is to use [Postman](https://www.postman.com).

To demonstrate the functionality a postman collection in ``docs/AsGoodAsNew.postman_collection.json``
is included in the project. 

Start postman and import the collection to use the API.

## Testing
For this PHP project automated testing is done via PHPUnit.

Since this projects includes integration tests with a test db you need to activate the test db 
before running the full test suite with the following commands:
- ``bin/console doctrine:database:create --env=test``
- ``bin/console doctrine:schema:update --env=test --force``

After that, tests can be run via execution into the container of the php application:
- ``docker exec -ti your-container-name bash -c 'export XDEBUG_MODE=coverage && php /app/bin/phpunit'``

This creates a code coverage report as html as well. The report can be found in the
``.phpunit.coverage.html`` director in the project root.

# Implementation Details

## Implement a simple RESTful API managing a shopping cart

### Freature Requirements/ Functionality
1. Einen Warenkorb anlegen
2. Einen Artikel in den Warenkorb legen
3. Einen Artikel aus dem Warenkorb löschen
4. Einen Artikel im Warenkorb editieren
5. Den Warenkorb anzeigen lassen

### Implementation Notes
- Use Symfony PHP Framework
- API needs to be RESTfull
- Do not use API Platform

### Acceptance criteria
- API follows REST
- API build on top of Symfony
- Functionality is implemented
- All tests are passing

### Endpoints
|   | URL                                     | METHOD | ACTION            | Request Body | Response Body |
|---|-----------------------------------------|--------|-------------------|--------------|---------------|
| 1 | /api/v1/carts                           | GET    | List Carts        | none         | json Data     |
| 2 | /api/v1/carts                           | POST   | Create empty Cart | none         | json Data     |
| 3 | /api/v1/carts/{cart_id}                 | GET    | Show Cart         | none         | json Data     |
| 4 | /api/v1/carts/{cart_id}                 | DELETE | Delete Cart       | none         | json Data     |
| 5 | /api/v1/carts/{cart_id}/items           | POST   | Add Cart Item     | json Data    | json Data     |
| 6 | /api/v1/carts/{cart_id}/items/{item_id} | PUT    | Update Cart Item  | json Data    | json Data     |
| 7 | /api/v1/carts/{cart_id}/items/{item_id} | DELETE | Delete Cart Item  | none         | json Data     |

### Endpoint Difinitions

## Application Storage
The API app uses Postgres as the storage engine via synfony's doctrine which holds the tables
The database data are held in the ``docker/pg_data`` as a bind mount volume from the postges instance in the same docker network.
The reset the data for testing purposes just delete the directory and ``docker compose down / up -d`` to rerun the migration  

1. ```cart``` for holding ```Cart``` data
2. ```cart_items``` for holding information about the ```CartItem```
3. ```doctrine_migration_versions``` for internal database setup and management 

The ``cart`` table holds fields for 

| name        | type           | default                             | Note                            |
|-------------|----------------|-------------------------------------|---------------------------------|
| id          | integer        | not null primary key                |                                 |
| code        | varchar(255)   | not null                            |                                 |
| total_price | integer        |                                     | automatically calculated in app |
| created_at  | timestamp(0)   | default CURRENT_TIMESTAMP not null  | (DC2Type:datetime_immutable)    |


The ``cart_item`` table holds fields for

| name       | type          | default                            | Note                          |
|------------|---------------|------------------------------------|-------------------------------|
| id         | integer       | not null primary key               |                               |
| cart_id    | integer       | constraint                         | references public.cart        |
| code       | varchar(255)  | not null                           |                               |
| name       | varchar(255)  | not null                           |                               |
| price      | integer       |                                    |                               |
| quantity   | integer       |                                    |                               |
| created_at | timestamp(0)  | default CURRENT_TIMESTAMP not null |  (DC2Type:datetime_immutable) |
