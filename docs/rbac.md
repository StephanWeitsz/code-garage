# RBAC Strategy

## Roles

- `super_admin`: unrestricted platform access through `Gate::before`
- `trainer`: teaching and operational permissions across courses, posts, queries, and supporting documents
- `student`: self-service permissions limited to personal learning, payments, and support workflows

## Permission Naming

Permissions use a `module.ability` pattern:

- `courses.view`
- `payments.collect`
- `posts.publish`
- `queries.resolve`
- `identity.roles.assign`

This keeps permissions readable, route-friendly, and easy to group by module.

## Middleware Usage

Prefer middleware for coarse route protection and policies for resource decisions.

Examples:

```php
Route::middleware(['auth:sanctum', 'role:super_admin|trainer'])->group(function () {
    Route::post('/courses', StoreCourseController::class);
});

Route::middleware(['auth:sanctum', 'permission:courses.view'])->group(function () {
    Route::get('/courses', ListCoursesController::class);
});
```

Use:

- `role:*` for dashboard areas reserved for a role family
- `permission:*` for action-specific API routes
- `role_or_permission:*` only when a route intentionally supports either check

## Policy Usage

Policies live inside each module under `Presentation/Policies`.

Suggested pattern:

- call `$this->authorize('viewAny', Course::class)` in controllers when model classes exist
- use policy methods for ownership checks and contextual rules
- keep permission strings centralized in `config/permissions.php`

Use middleware to enter a module boundary, then policies to decide on individual resources.
