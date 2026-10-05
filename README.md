# ICONIK.CO careers application

PHP application form with an applicant status page.

## Pages

- `index.php` — Step 1: details and CV
- `experience.php` — Step 2: experience
- `review.php` — Step 3: review and submit
- `thanks.php` — confirmation with reference number
- `status.php` — applicants look up hired / not selected / in review
- `admin.php` — hiring team updates status

Applications are stored in `data/applications.json`. Uploaded CVs go in `uploads/`.

## Run locally

```bash
php -S localhost:8080 -t .
```

Then open:

- Apply: http://localhost:8080/index.php
- Status: http://localhost:8080/status.php
- Hiring desk: http://localhost:8080/admin.php

Default admin password: `iconik-admin` (change this in `admin.php` before using in production).

## Sample status lookups

| Email | Reference | Status |
| --- | --- | --- |
| amina.okeke@example.com | IK-20260910-A1B2C3 | Hired |
| jonah.reed@example.com | IK-20260908-D4E5F6 | Not selected |
| priya.shah@example.com | IK-20260915-AABB11 | In review |

Give each applicant their reference number after they submit. They check status with that number plus the email they applied with.
