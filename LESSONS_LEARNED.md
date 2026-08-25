# Lessons Learned

These notes summarize the implementation milestones in this repository while following Master.dev's **Laravel Fundamentals** course with Leah Thompson.

## 1. Start with the framework conventions

The project began from Laravel's React starter foundation. The first milestone established authentication, Fortify actions, settings controllers, Inertia layouts, reusable UI components, and automated tests.

Key lessons:

- Laravel's conventions provide a strong application structure before feature code is added.
- Fortify keeps authentication concerns organized into focused actions and controllers.
- Inertia allows server-side Laravel routing and validation to work naturally with a React UI.
- Shared React components and layouts prevent authentication and settings pages from becoming repetitive.
- Security-sensitive operations such as password changes and two-factor settings should have explicit middleware and throttling.

## 2. Model the domain in the database first

The events milestone introduced `meetup_groups` and `events` tables, Eloquent models, relationships, seed data, and event pages.

Key lessons:

- Foreign keys and cascade rules express ownership and cleanup behavior in the schema.
- A slug is useful for readable event URLs and can be configured through `getRouteKeyName()`.
- Eloquent relationships make the domain readable: a user owns meetup groups, a group has events, and users can attend many events.
- Seeders make the UI easier to develop and verify with realistic data.
- Database column types matter. Date/time columns should be cast to Carbon objects in the model when application code needs date methods.

## 3. Keep validation and authorization separate

Event creation and editing use dedicated form request classes, while event ownership rules live in `EventPolicy`.

Key lessons:

- Form requests keep controller methods small and make validation reusable.
- Validation answers whether input is well formed; policies answer whether the current user is allowed to perform an action.
- Protecting create, update, and RSVP routes with the appropriate authentication and verification middleware prevents accidental access from the frontend alone.
- Server-side authorization remains necessary even when the React interface hides unavailable actions.

## 4. Use a pivot table for RSVP state

RSVP support introduced the `event_user` many-to-many table and a unique event/user constraint.

Key lessons:

- A pivot table is the right representation when users can attend multiple events and events can have multiple users.
- `belongsToMany()` keeps RSVP operations expressive through `attach()` and `detach()`.
- A database uniqueness constraint protects against duplicate RSVPs even if two requests arrive at the same time.
- The frontend should reflect server state and use the same route names as the backend.

## 5. Move email work to a queue

The final course milestone added `SendRsvpEmail`, `RsvpConfirmation`, and a Markdown Blade email template.

Key lessons:

- Queuing email keeps the RSVP request responsive and moves slow or failure-prone work to a worker.
- Jobs should receive the domain objects they need and keep orchestration simple.
- A mailable separates message metadata, subject configuration, and presentation from the controller.
- The `log` mail driver is useful for checking rendered output locally, but it is not a delivery service.
- A queue worker must be running for database-backed jobs to be processed.

## 6. Read the logs before debugging the worker

The queue output only reported `FAIL`, but `storage/logs/laravel.log` contained the actual exception:

```text
Call to a member function format() on string
resources/views/emails/rsvp-confirmation.blade.php:10
```

The email view called `format()` on `starts_at`, while the model returned the database value as a string. This is a useful debugging pattern:

1. Find the job's stack trace in the application log.
2. Identify the first application-owned file and line.
3. Check the type of the value used at that line.
4. Fix the model or boundary where the type should have been normalized.

The appropriate model-level correction is:

```php
protected function casts(): array
{
    return [
        'starts_at' => 'datetime',
    ];
}
```

## 7. Deployment changes the infrastructure assumptions

Local development uses SQLite, a database queue, and logged mail. Production needs shared, durable services:

- PostgreSQL or another managed database for application data and queued jobs.
- A continuously running worker for `SendRsvpEmail`.
- A real mail transport.
- Environment-specific secrets and an application key.
- A Docker-based PHP runtime on platforms that do not provide native PHP services.

The main deployment lesson is that application code and infrastructure are connected: changing from a local process to a cloud service also changes storage, queues, email delivery, and process management.

## 8. Next improvements

- Add feature tests for event creation, authorization, RSVP/cancellation, and email dispatch.
- Add a datetime cast for `Event::$starts_at` and verify the email job with a test mailer.
- Add a Dockerfile and Render service configuration if Render deployment is selected.
- Use PostgreSQL and a production mail provider in the deployed environment.
- Consider retry, timeout, and failed-job monitoring for the email worker.
