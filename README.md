# Clinic Appointment System API

A RESTful API built with Laravel 12 for managing a clinic appointment system.

The system supports authentication, role-based access control, appointment scheduling, doctor schedule management, and patient self-booking.

---

## Features

### Authentication

- Patient Registration
- User Login & Logout
- Email Verification
- Password Reset
- Laravel Sanctum Authentication

### Receptionist

- Manage Doctors (CRUD)
- Manage Patients (CRUD)
- Manage Appointments (CRUD)
- Search & Pagination

### Doctor

- View Assigned Appointments
- Update Appointment Status
- Manage Weekly Schedule

### Patient

- Book Appointment
- View Personal Appointments
- Update Profile

### General

- Role-Based Authorization
- Form Request Validation
- API Resources
- Standard JSON Responses
- Eloquent Relationships

---

## Tech Stack

- PHP 8
- Laravel 12
- Laravel Sanctum
- MySQL
- REST API

---

## User Roles

- Receptionist
- Doctor
- Patient

---

## Installation

```bash
git clone https://github.com/ahmaddura49-rgb/clinic-appointment-system-api.git

cd clinic-appointment-system-api

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan serve
```

---

## Future Improvements

- Swagger API Documentation
- Unit & Feature Testing
- Dashboard Statistics
- Docker Support

---

## Author

**Ahmad Dura**

Backend Developer (Laravel)
