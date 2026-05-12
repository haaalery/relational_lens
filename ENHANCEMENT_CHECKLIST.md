# Relational Lens - Enhancement Checklist

This checklist tracks the implementation of security, stability, and architectural improvements for the Relational Lens project.

## Why are we doing this?
- **Security:** To protect user data from hackers (XSS protection) and prevent sensitive information leaks (database error handling).
- **Stability:** To ensure that database records remain consistent and that URLs (slugs) never collide or break.
- **Reliability:** To ensure that only valid data enters the system through robust server-side validation.
- **Maintainability:** To bring the project up to professional standards (using environment variables and standard auth checks).

---

## Phase 1: Critical Security & Integrity
- [x] **Fix XSS Vulnerability in `article.php`**
    - *Why:* Prevents malicious scripts from being executed in users' browsers.
- [x] **Secure Database Connection Feedback (`config/db.php`)**
    - *Why:* Hides technical database details from the public if a connection fails.
- [x] **Implement Server-Side Validation (`register.php`, `submit.php`, `submit_article.php`)**
    - *Why:* Ensures that data is valid even if a user bypasses the browser's "required" fields.

## Phase 2: Database & Core Logic
- [x] **Enforce Unique Slugs (`relational_lens.sql`)**
    - *Why:* Prevents multiple stories from having the same URL, which would cause confusion.
- [x] **Handle Slug Collisions (`submit.php`, `submit_article.php`)**
    - *Why:* Automatically fixes duplicate titles by adding a suffix (e.g., `my-story-2`) instead of crashing the site.
- [x] **Add Transaction Support (`submit_article.php`)**
    - *Why:* Ensures that if an error occurs during submission, no "partial" or broken data is left in the database.

## Phase 3: Architecture & Standardization
- [x] **Centralize Environment Configuration (`.env`)**
    - *Why:* Keeps your database password out of the code files for better security.
- [x] **Verify Admin Authorization (`admin/*.php`)**
    - *Why:* Double-checks that only authorized users can access administrative tools.
- [x] **Standardize Asset Injection**
    - *Why:* Makes the code cleaner and easier to manage as the project grows.

## Phase 4: UI/UX & Functional Polish
- [x] **Update "Forgot Password" Link (`login.php`)**
    - *Why:* Fixes a broken link to improve user experience.
- [x] **Improve Empty States (index, archive)**
    - *Why:* Provides a professional look even when there is no content to show yet.

## Phase 5: Media & Functional Upgrades
- [x] **Implement Image Upload System**
    - *Why:* Replaces manual URL inputs with a secure file upload system for thumbnails and featured images.
- [ ] **Real Password Recovery System**
    - *Why:* Allows users to reset their own passwords securely via email.
- [ ] **Scholarly Article Search**
    - *Why:* Improves discoverability of content in The Gallery.

---
*Created on May 12, 2026*
