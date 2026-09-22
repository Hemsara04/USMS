# University Sports Management System (USMS)

> A centralized, role-based web application engineered for university athletic administration, sports equipment logistics, clinical medical clearances, facility scheduling, and real-time squad management.

Developed as a **2nd Year Software Engineering Group Project** for the University of Colombo / Physical Education Department.

---

## 📄 Academic Project Documentation
- **[Formal Academic Interim Report](docs/USMS_Interim_Report.md)**: Comprehensive report adhering strictly to the university evaluation rubric, including:
  - Domain analysis, current limitations, and SMART objectives
  - 4-Dimensional Feasibility Study (Technical, Operational, Economic, Schedule)
  - Functional & Non-Functional Requirements Specification (6 System Actors)
  - 3-Tier MVC Architecture & Component Interaction Flows
  - Complete System Design Diagrams (Use Case, Class, 20-Table ERD, Activity Diagrams in Mermaid)
  - Current Progress Matrix (~65% completed) and 4-Member Contribution Breakdown

---

## 🛠️ Technology Stack
- **Backend Architecture:** PHP 8.2+ (MVC Architecture, Zero external framework overhead)
- **Database Persistence:** MySQL 8.0 / MariaDB (InnoDB, 20 normalized relational tables, PDO singleton)
- **Frontend Presentation:** HTML5, Modern Vanilla CSS (`assets/css/dashboard.css`, `assets/css/auth.css`), Vanilla JavaScript (`assets/js/dashboard.js`)
- **Security:** Bcrypt password hashing, session-bound Role-Based Access Control (RBAC) via `config/auth_guard.php`, PDO parameterized prepared statements

---

## 👥 User Roles & Portals
1. **Teacher-in-Charge (TIC) / Sports Council Director:** Equipment inventory, checkout logs, facility reservations, squad final approvals.
2. **University Medical Officer:** Athlete health registry, medical clearance issuance, acute injury records, physiotherapy treatments.
3. **Student Sports Council President:** Broadcast announcement engine, urgent priority alerts, global notification dispatcher.
4. **Sports Coach / Manager:** Squad drafting, training session schedules, attendance tracking, fixture results.
5. **Student Athlete / Player:** Self-service profile, equipment requisition, sport applications, medical clearance status.
6. **System Administrator:** User account provisioning, sports catalog, audit logs.
