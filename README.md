# 🌿 Beauty Italy Factory ERP — Phase 1 (Controlled Core)

[![GMP Ready](https://img.shields.io/badge/GMP-ISO%2022716-emerald?style=flat-square)](https://www.iso.org/standard/36437.html)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square&logo=laravel)](https://laravel.com)
[![MySQL](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-blue?style=flat-square&logo=mysql)](https://mysql.com)
[![React](https://img.shields.io/badge/Frontend-React%2018%20%2B%20Tailwind-indigo?style=flat-square&logo=react)](https://react.dev)

A modern, regulated B2B Manufacturing Execution and Enterprise Resource Planning (ERP) system designed specifically for **Beauty Italy Factory** (Cosmetics & Skincare Manufacturing) under strict **ISO 22716 (Cosmetics GMP)** and **21 CFR Part 11** guidelines.

---

## 🌟 Overview & Phase 1 Scope

Phase 1 establishes the **Controlled Core** of the manufacturing lifecycle — ensuring that no material is consumed, weighed, or shipped without rigorous lot tracking, electronic batch verification, and segregation of duties.

### Key Capabilities Included:
1. **Operations Command Center:** Live MES dashboard displaying active production line progress, real-time lot quarantine alerts, FEFO expiration countdowns, and pending QA disposition statuses.
2. **Inventory & Lot Ledger (WMS):** Rigorous lot control with status quarantine (`Quarantine` 🟧, `Approved` 🟩, `Rejected` 🟥), FEFO expiration monitoring, and an **append-only, immutable transaction ledger**.
3. **Formulation & Ingredient Studio (PLM / R&D):** Complete product development workspace featuring INCI ingredient master data, Phase A/B/C batch formulation scaling, real-time 100% total weight validation, and a multi-stage GMP approval workflow.
4. **Shopfloor Tablet Dispensing (eBMR / MES):** Touch-optimized interface for production workers at weighing station WS-02. Features digital barcode verification of raw material lots, live digital scale readouts with dynamic tolerance indicators (±0.5%), and electronic step sign-offs.
5. **Quality Control & QA Batch Release (QC / LIMS / QMS):** Physical & chemical lab testing (pH, viscosity, microbiology), Certificate of Analysis (COA) records, strict **Segregation of Duties**, and **21 CFR Part 11 Electronic Signatures** for final commercial batch release.
6. **End-to-End Genealogy & Traceability:** Interactive visual Sankey/Tree diagram connecting Supplier Purchase Orders ➡️ Raw Material Lots ➡️ Production Batches ➡️ Finished Goods Lots ➡️ Customer Delivery Notes, with instantaneous mock-recall capability.
7. **Role-Based Access Control (RBAC):** Built-in role switcher adapting the UI context across **Admin**, **Warehouse Operator**, **R&D Chemist**, **Shopfloor Operator**, **QC Lab Analyst**, and **QA Manager**.

---

## 💻 Tech Stack

* **Backend Framework:** [Laravel 11.x](https://laravel.com/)
* **Database:** MySQL 8.0+ / MariaDB 10.4+ (utf8mb4_unicode_ci collation)
* **Frontend:** Interactive Single Page Application powered by React 18, Babel Standalone & Tailwind CSS
* **Architecture:** Domain-Driven Modular Monolith with RESTful API endpoints (`/api/v1/...`)

---

## 🚀 Installation & Getting Started

### Prerequisites
Make sure you have the following installed on your machine:
* **PHP** >= 8.2 with `pdo_mysql` enabled (e.g. via XAMPP or native PHP)
* **Composer** (v2.x)
* **MySQL** or **MariaDB** (running on port `3306`)

---

### Step-by-Step Setup

#### 1. Clone the repository
```bash
git clone https://github.com/omaramer38/Beauty-Italy-Factory-.git
cd Beauty-Italy-Factory-
```

#### 2. Configure Environment
Navigate into the `backend` folder and copy `.env.example` to `.env`:
```bash
cd backend
copy .env.example .env
```
*(On Linux/macOS use `cp .env.example .env`)*

Ensure your `.env` contains the database configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=beauty_italy_erp
DB_USERNAME=root
DB_PASSWORD=
DB_COLLATION=utf8mb4_unicode_ci
```

#### 3. Create the MySQL Database
Make sure your MySQL service is started (e.g. from XAMPP Control Panel), then run:
```sql
CREATE DATABASE IF NOT EXISTS beauty_italy_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 4. Install Composer Dependencies (if not already installed)
```bash
composer install
```

#### 5. Generate Application Key
```bash
php artisan key:generate
```

#### 6. Run Migrations & Seed Sample Factory Data
```bash
php artisan migrate --seed
```
*This command creates all Phase 1 tables and seeds real cosmetics manufacturing data (raw materials, lots, formulations, active line batches, and lab test results).*

#### 7. Start the Server
```bash
php artisan serve
```

#### 8. Open the Application
Open your web browser and navigate to:
```
http://127.0.0.1:8000
```

---

## 🧪 Phase 1 REST API Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/v1/dashboard` | Returns live KPIs, shift status, and line progress |
| `GET` | `/api/v1/lots` | Returns all lots with filter, search, and FEFO expiry data |
| `GET` | `/api/v1/lots/{lot}/ledger` | Fetches the immutable movement history for a lot |
| `GET` | `/api/v1/formulations/{code}` | Fetches formulation lines, phases, and signature approvals |
| `GET` | `/api/v1/dispensing/{batch}` | Returns shopfloor dispensing sequence and scale status |
| `POST` | `/api/v1/dispensing/sign` | Electronically signs an ingredient weighing step |
| `GET` | `/api/v1/quality/{sample}` | Returns laboratory physical/chemical test parameters |
| `POST` | `/api/v1/quality/release` | Applies QA 21 CFR Part 11 electronic signature for batch release |
| `GET` | `/api/v1/traceability/{lot}` | Returns the full supplier-to-customer genealogy tree |

---

## 👥 Factory Personas (Roles & Permissions)

* **Admin (Marco Conti):** Full executive oversight, audit logs, and system configuration.
* **Warehouse Operator (Giulia Romano):** Goods receipt into quarantine, FEFO picking, storage location tracking.
* **R&D Chemist (Dr. Sofia Bianchi):** Formulation engineering, INCI specification, stability and version control.
* **Shopfloor Operator (G. Romano):** Weighing station barcode scanning, precision dispensing, and eBMR recording.
* **QC Lab Analyst (Luca Bellini):** Sample testing, physical/chemical assay recording, microbiology verification.
* **QA Manager (Elena Rossi):** Independent batch audit, deviation disposition, and final electronic release signature.

---

## 📄 License & Compliance

Proprietary software developed for **Beauty Italy Factory**. Designed in alignment with **ISO 22716 Good Manufacturing Practices (GMP)**.
