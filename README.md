# ltw14g06

## Features

**All users:**
- [X] Register a new account.
- [X] Log in and out.
- [X] Edit their profile, including name, username, password, and profile photo.

**Members:**
- [X] Browse the schedule of available fitness classes, filtering by type, trainer, day, or time.
- [X] Enroll in and cancel enrollment from upcoming classes, subject to capacity limits.
- [X] View trainer profiles, including their specializations and the classes they teach.
- [X] Check the current availability of equipment in the main training area.
- [X] Leave ratings and reviews for classes they have attended.

**Trainers:**
- [X] Manage their public profile, including bio, specializations, and certifications.
- [X] View the roster of members enrolled in their classes.
- [X] Track and manage their assigned class schedule.

**Admins:**
- [X] Manage members and trainers (create, update, and deactivate accounts).
- [X] Manage the class catalog (create, edit, and remove classes) and assign trainers to them.
- [X] Manage equipment in the main training area (add, update availability status, and remove items).
- [X] Elevate a user to admin status.
- [X] Oversee and ensure the smooth operation of the entire sys2tem.

**Extra:**
- [X] Class bookings
- [X] Membership plans
- [X] Gym Shop & Cart
- [X] Forum



## Running

**Locating the PHP files:**

- `admin_dashboard.php`: The administration dashboard page (located in the `pages/` folder).
- `checkout_plan.php`: The plan checkout page (located in the `pages/` folder).
- `create_class.php`: The class creation page (located in the `pages/` folder).
- `edit_class.php`: The class editing page (located in the `pages/` folder).
- `forum.php`: The community forum page (located in the `pages/` folder).
- `login.php`: The user login page (located in the `pages/` folder).
- `plan.php`: The workout schedule page (located in the `pages/` folder).
- `profile.php`: The profile page (located in the `pages/` folder).
- `register.php`: The user registration page (located in the `pages/` folder).
- `review.php`: The class review page (located in the `pages/` folder).
- `shop.php`: The gym shop and cart page (located in the `pages/` folder).
- `trainer_dashboard.php`: The trainer dashboard page (located in the `pages/` folder).
- `trainer.php`: The trainer profile page (located in the `pages/` folder).
- `index.php`: The main landing page (located in the root folder).

**Starting the server:**
To see the rendered result properly via PHP, open your terminal in the root folder of the project and run the following commands:

    sqlite3 database/database.db < database/database.sql
    php -S localhost:9000

