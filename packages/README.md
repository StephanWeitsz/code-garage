# Code Garage Packages

This directory contains first-party bounded-context modules for Code Garage.

Each module follows the same structure:

- `src/Domain`: business rules, repository contracts, value objects, domain events
- `src/Application`: use-case services, listeners, DTOs
- `src/Infrastructure`: persistence and external integrations
- `src/Presentation`: controllers, requests, API resources
- `src/Providers`: Laravel service providers
- `config`: module configuration
- `routes`: module routes
- `database`: module migrations, factories, seeders

Use `packages/Shared/stubs/module` as the starting template for new modules.


