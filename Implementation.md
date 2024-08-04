# Implementation

## Implement an API managing a shopping cart

### Functionality

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
|   | URL                                     | METHOD | ACTION           | Request Body |
|---|-----------------------------------------|--------|------------------|--------------|
| 1 | /api/v1/carts                           | GET    | List Carts       | none         |
| 2 | /api/v1/carts                           | POST   | Create Cart      | json Data    |
| 3 | /api/v1/carts/{cart_id}                 | GET    | Show Cart        | none         |
| 4 | /api/v1/carts/{cart_id}                 | PUT    | Update Cart      | json Data    |
| 4 | /api/v1/carts/{cart_id}                 | DELETE | Delete Cart      | none         |
| 5 | /api/v1/carts/{cart_id}/items           | POST   | Add Cart Item    | json Data    |
| 6 | /api/v1/carts/{cart_id}/items/{item_id} | PUT    | Update Cart Item | json Data    |
| 7 | /api/v1/carts/{cart_id}/items/{item_id} | DELETE | Delete Cart Item | none         |
