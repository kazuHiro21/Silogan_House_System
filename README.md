# Jun's Silogan House - Midterm Exam Project

This project continues the **Jun's Silogan Shop one-to-many activity**. That activity introduced the relationship between a shop and its menu items. This midterm project builds on that idea by adding a user entity, registration, login, logout, access protection, and a record of who changed the data.

The system is a small shop management website for a silogan business. A signed-in user can manage shop details and the silog meals offered by each shop.

## Relationship between the records

One shop can have many menu items. Each menu item belongs to one shop through `menu_items.shop_id`.

```text
users
  |-- creates or updates --> shops
  |-- creates or updates --> menu_items
  |-- performs actions --> activity_logs

shops (one) ---------------- (many) menu_items
```

The `users` table stores each account's first name, last name, email, password hash, and optional address and age. Both shops and menu items store `added_by`, `updated_by`, and `last_updated`. The `activity_logs` table records insert, update, and delete actions with the user's ID, the affected record type, ID and name, and the action time. Record changes and their audit events are committed together.

All four tables are in the same MySQL database named `silogan_db`. The SQL files describe how to create or upgrade the database; PHP stores the actual account, shop, menu, and activity rows in MySQL.

## How the system works

1. **Register:** `register.php` checks the submitted details, makes sure the email is not already used, hashes the password, and saves the new user in `users`.
2. **Log in:** `login.php` finds the account by email and checks the submitted password against its saved hash. A successful login starts a PHP session.
3. **Protect the dashboard:** `index.php` and the shop and menu handlers check that a user session exists. Visitors who are not signed in are sent to the login page.
4. **Manage records:** The dashboard shows shops and their menu items. The insert, update, and delete handlers save changes to MySQL using prepared statements.
5. **Track changes:** The current user's ID is saved with new or updated shop and menu records. Each action is also written to `activity_logs`, including the record name, which the dashboard displays as recent activity.
6. **Log out:** `logout.php` clears the session and returns the user to the login page.

Passwords are stored as hashes, not as readable passwords. Existing shop or menu rows created before account tracking have no known creator, so their `added_by` field is empty. Audit entries keep the actor, action, record type, record ID, record name, and timestamp. Names are saved with the event so deletions remain understandable after the original row is gone.

## Project files

```text
AppDev_Exam/
├── assets/
│   └── style.css          # Page layout and styling
├── core/
│   ├── dbconfig.php       # MySQL connection settings
│   ├── functions.php      # Session, login check, and shared helpers
│   ├── schema.sql         # Tables for a new database
│   └── upgrade.sql        # One-time upgrade for the older shop database
├── menu_items/
│   ├── insert.php
│   ├── update.php
│   └── delete.php
├── shops/
│   ├── insert.php
│   ├── update.php
│   └── delete.php
├── index.php              # Protected shop dashboard
├── register.php
├── login.php
└── logout.php
```

## Run the project with XAMPP

1. Put the project folder in `C:\xampp\htdocs\AppDev_Exam`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. For a new database, import `core/schema.sql`. If `silogan_db` already has the older `shops` and `menu_items` tables, run `core/upgrade.sql` **once** instead. Do not run the upgrade script repeatedly; it adds columns and keys to the existing tables, including the record-name field for audit history.
   If you already ran the earlier version of `schema.sql` or `upgrade.sql`, do not rerun `upgrade.sql`; add the audit-name column once with `ALTER TABLE activity_logs ADD COLUMN record_label VARCHAR(255) DEFAULT NULL;`.
5. If your MySQL connection settings differ from the XAMPP defaults, update the host, username, password, or database name at the top of `core/dbconfig.php`.
6. Open `http://localhost/AppDev_Exam/`, register an account, and log in.

## Database table summary

| Table | What it stores |
|---|---|
| `users` | Account and personal information, plus the password hash |
| `shops` | Silogan shop details and user IDs for creation and updates |
| `menu_items` | Meals, prices, descriptions, parent shop ID, and user tracking |
| `activity_logs` | Who inserted, updated, or deleted each shop or menu item, its name, and when |

## Database library note

The earlier activity is referred to as the **one-to-many PDO activity**. The PHP files in this midterm project currently use **MySQL** with prepared statements for database access. The relationship and project idea continue from that activity, but the current code is not written with PDO.
