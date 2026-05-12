# 🗺️ Relational Lens: Master Plan & Project Documentation
*Last Updated: May 7, 2026*

---

## 📑 Table of Contents
1. [📊 Executive Summary & Progress](#-executive-summary--progress)
2. [🛡️ Security & UX Roadmap (Audit May 2026)](#-security--ux-roadmap-audit-may-2026)
3. [🚀 Operational Roadmap (Checklist)](#-operational-roadmap-checklist)
4. [🎨 Brand & Style Identity](#-brand--style-identity)
5. [📸 Asset & Content Audit](#-asset--content-audit)
6. [💾 Technical Architecture & Database](#-technical-architecture--database)

---

## 📊 Executive Summary & Progress

### Overall Status: **Core Ready & Polished**
The Relational Lens platform has transitioned from a structural "Foundation" phase to a "Content Engine" stage. All pages now share a unified, premium layout system with standardized visual consistency.

| Category | Status | Details |
| :--- | :--- | :--- |
| **Architecture** | ✅ 100% | Centralized `header.php`/`footer.php` across all pages. |
| **Authentication** | ✅ 100% | Secure sign-in/register with theme-aware styling. |
| **Archive & Map** | ✅ 100% | Interactive Leaflet.js map and 16:9 story grid. |
| **Submissions** | ✅ 100% | Multi-step wizards for both Stories and Articles. |
| **Admin Panel** | ✅ 100% | Unified sidebar dashboard with dark mode support. |
| **Dark Mode** | ✅ 100% | Robust toggle with scroll persistence and high contrast. |

---

## 🛡️ Security & UX Roadmap (Audit May 2026)

Following a comprehensive audit of the codebase, the following improvements are prioritized to protect the platform and enhance inclusivity.

### 1. Security Hardening (Highest Priority)
*   **Harden Sessions:** Update `config/security.php` to enforce `HttpOnly` and `Secure` cookie flags to prevent session hijacking.
*   **Input Sanitization:** Implement a global `sanitize()` function to proactively strip dangerous tags from all `$_POST` and `$_GET` data.
*   **Password Policy:** Enforce minimum strength requirements (length, complexity) during registration.
*   **Rate Limiting:** Add a cooldown period after multiple failed login attempts to thwart brute-force bots.

### 2. User Experience (Flow & Feedback)
*   **Form Auto-Save:** Implement local storage persistence in `submit.php` and `submit_article.php` so contributors don't lose progress on refresh.
*   **Loading UI:** Add premium "Glassmorphism" loading overlays during form submissions to prevent double-clicks.
*   **Submission History:** Provide a clear "Status" view in the User Profile so filmmakers can track their work through the Review Queue.

### 3. Global Accessibility (Inclusivity)
*   **ARIA Optimization:** Add missing `aria-label` tags to the theme toggle, social icons, and dropdown menus for screen-reader compatibility.
*   **Focus Management:** Ensure keyboard focus moves logically into opened dropdowns and modal states.
*   **Skip Links:** Add a "Skip to Main Content" link for users navigating via keyboard.

---

## 🚀 Operational Roadmap (Checklist)

### ✅ Phase 1-3: Foundations & Narrative
- [x] Secure PDO connection and Database schema.
- [x] Mission & Vision pages with brand identity.
- [x] Advanced Search & Interactive Map integration.

### ✅ Phase 4-5: The Collective & The Gallery
- [x] User Profiles & Submission Wizard (Ethics/Consent).
- [x] Database Migration for the Article system.
- [x] "The Gallery" masonry grid for Photo Essays & Reflections.

### ✅ Phase 6: Admin Ecosystem
- [x] Sidebar-driven dashboard unification.
- [x] Peer-Review workflow (Pending -> Under Review -> Approved).
- [x] Content management for Taxonomy (Categories & Regions).

### 🟡 Phase 7: Launch Readiness (Current)
- [x] Mobile optimization for floating navbar.
- [ ] Implement Security Hardening from May Audit.
- [ ] Conduct final A11y (Accessibility) sweep.

---

## 🎨 Brand & Style Identity

### Visual Principles
*   **Glassmorphism:** Use `backdrop-filter: blur(15px)` for the navbar and dropdowns to create depth.
*   **Micro-Interactions:** 1.1x scale-up and rotation on hover for interactive elements (cards, icons).
*   **16:9 Consistency:** All documentary and article thumbnails are strictly cropped to 16:9 to maintain grid alignment.

### Color Palette
*   **Navy (`#0E3A47`):** Professionalism & Trust.
*   **Terracotta (`#C57D54`):** Warmth & Human Connection.
*   **Sage (`#4B8759`):** Growth & Healing.
*   **Sand (`#E5CBAA`):** Foundation & Grounding.

---

## 📸 Asset & Content Audit

| Asset Type | Requirement | Status |
| :--- | :--- | :--- |
| **Hero Images** | High-res local assets for Mission, About, and Insights. | 🟡 Placeholders in use. |
| **Story Thumbnails** | 16:9 covers for all 10+ founding stories. | 🟢 80% Populated. |
| **Guides** | PDF Toolkits for educational collections. | 🔴 Missing in `assets/guides/`. |
| **Avatars** | Team and User profile portraits. | 🟡 Generic icons in use. |

---

## 💾 Technical Architecture & Database

### Core Tables
1.  **`users`**: Handles auth, roles (Admin/Reviewer/Filmmaker), and bios.
2.  **`stories`**: The documentary archive (Video URLs, Transcripts, Ethics).
3.  **`articles`**: Long-form scholarship and photo essays.
4.  **`regions` / `categories`**: Global taxonomy for the Map and Search.
5.  **`consent_records`**: Digital "paper trail" for ethical storytelling.

### Security Implementation
*   **PDO Prepared Statements:** Absolute prevention of SQL Injection.
*   **CSRF Tokens:** Session-based tokens validated on every POST request via `config/security.php`.
*   **Password Hashing:** `PASSWORD_DEFAULT` (Bcrypt) for all user credentials.
