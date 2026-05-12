# 🚀 Relational Lens: Enhancement Plan V2.0
*Design & Security Evolution*

This plan outlines the "Next-Gen" improvements for Relational Lens, moving beyond the functional foundation to a premium, high-security digital ecosystem.

---

## 🎨 Part 1: Design & Aesthetic Evolution
*Goal: Move from "functional" to "immersive" through motion, depth, and performance.*

### 1. Motion Design & Micro-Orchestration
*   **Staggered Entrance:** Replace simple `reveal` classes with a GSAP-lite or Intersection Observer script that staggers the appearance of grid items (cards) for a more "expensive" feel.
*   **Contextual Cursor:** Implement a custom "magnetic" cursor that changes shape when hovering over play buttons or interactive map markers.
*   **Seamless Page Transitions:** Use a small PJAX or Barba.js implementation to allow for persistent audio/video playback while navigating between pages.

### 2. Performance-First Visuals
*   **Skeleton Screens:** Replace the planned "Glassmorphism loading overlay" with animated skeleton cards. This reduces perceived load time and prevents layout shifts.
*   **WebP/Avif Migration:** Automate the conversion of user-uploaded thumbnails to modern formats to keep the Archive page lightning-fast.
*   **Variable Typography:** Transition to variable font weights for `Inter` to allow for finer control over hierarchy and readability.

### 3. Data Visualization (The "Insights" Layer)
*   **Relationship Mapping:** On the `Insights` page, use D3.js or Chart.js to create a "Relational Graph" showing how different stories and regions are interconnected by themes.
*   **Interactive Map Layers:** Add "Heatmap" or "Clustering" to the Leaflet.js map to visualize the density of stories across different global regions.

---

## 🛡️ Part 2: Security & Structural Integrity
*Goal: Transition from basic protection to an "Enterprise-Grade" defensive posture.*

### 1. Advanced Header Security
*   **Content Security Policy (CSP):** Implement a strict CSP in `header.php` to prevent XSS by only allowing trusted scripts (e.g., Bootstrap CDN, Leaflet) and disabling inline scripts.
*   **HSTS & Security Headers:** Force HTTPS and implement `X-Frame-Options: DENY` (Clickjacking protection) and `X-Content-Type-Options: nosniff`.
*   **Subresource Integrity (SRI):** Add `integrity` hashes to all third-party CDN links (Bootstrap, Icons, Leaflet).

### 2. Defensive User Authentication
*   **2FA (Two-Factor Authentication):** Implement TOTP (Google Authenticator) specifically for the **Superadmin** and **Reviewer** roles.
*   **JWT for API-Lite Routes:** If the site moves toward more async JS fetching, use JWT tokens for stateless, secure data retrieval.
*   **Account Lockdown:** Implement a "Soft-Lock" policy where accounts are disabled for 15 minutes after 5 failed attempts, with an automated email notification to the user.

### 3. Secure Content Lifecycle
*   **File Upload Hardening:** 
    *   Rename all uploaded assets to random UUIDs to prevent directory traversal.
    *   Store uploads outside the web root (if possible) or use `.htaccess` to disable script execution in the `uploads/` folder.
*   **Audit Logging:** Create a `system_logs` table that records every administrative action (Approvals, Deletions, Profile Edits) with Timestamp and IP address.

---

## 🛠️ Part 3: Implementation Roadmap

| Milestone | Task | Priority |
| :--- | :--- | :--- |
| **A: Security Pulse** | Implement CSP, SRI, and Header Hardening. | High |
| **B: Smooth Flow** | Integrate Skeleton Screens and Staggered Animations. | Medium |
| **C: Admin Guard** | Develop 2FA for Admin panel and Audit Logs. | High |
| **D: Visual Depth** | Refine Typography and Implement Soft-Shadow/Depth system. | Medium |
| **E: Data Insights** | Build the Relational Graph for the Insights page. | Low |

---

## 📜 Technical Brief for Developers
*   **Refactor Target:** Centralize all input sanitization into a `Security` class instead of procedural functions.
*   **CSS Update:** Move from fixed colors to HSL-based CSS variables to allow for "Soft Dark" vs "Deep Dark" mode transitions.
*   **Database:** Add `last_login_ip` and `failed_login_count` to the `users` table.
