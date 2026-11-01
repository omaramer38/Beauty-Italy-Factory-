# Beauty Italy ERP Vision – Analysis & Three-Phase Plan

Planning document · Nov 1, 2026 · Status: for review, no implementation started

## 1. Business Understanding

Beauty Italy Factory wants one connected system that follows a product from formula to cash collected, so that no department works from its own spreadsheet or paper record. Everything below separates what the client stated from what we recommend.

### What the client actually wants

- **One source of truth.** R&D, production, QC, QA, warehouse, purchasing, sales, finance, HR, maintenance and export all read and write the same data.
- **GMP-grade control.** Electronic batch records, lot traceability, quarantine/approved/rejected stock, approval workflows and audit trails, aligned with ISO 22716 (compliance must be validated, not claimed).
- **Planning that prevents shortages.** MRP driven by orders, approved formulas, stock, open POs, capacity and lead times.
- **True cost and profit visibility.** Actual batch cost against standard cost, by product and by customer.
- **An intelligence layer later.** Forecasting, anomaly detection, computer vision and a natural-language executive assistant, built on trusted ERP data.
- **An executive view.** A responsive CEO Command Center for computers, tablets and phones.

### Main business objectives

1. Full traceability from supplier lot to customer delivery.
2. Fewer material shortages, excess stock and production delays.
3. Faster, auditable quality release of batches.
4. Reliable per-batch costing and profitability.
5. Less waste and rework on the filling and labeling lines.
6. Management decisions based on live data instead of manual reports.

### Operational problems named by the client

| Problem | Source | Nature |
| --- | --- | --- |
| Filling machines sometimes under- or over-fill containers | Client-stated | Quality and yield loss |
| Labeling machine places stickers incorrectly, needing manual adjustment | Client-stated | Lost production time, rework |
| Disconnected departments (implied by the integration goal) | Inferred from scope | Needs confirmation in discovery |

### Confirmed, recommended and assumed

- **Confirmed:** Laravel as the foundation; the 12 modules in the brief; exactly three delivery phases; AI as a later layer; human approval for critical quality and business decisions; AI limited to the requesting user's permissions.
- **Recommendation (ours):** modular monolith, phase distribution, and technology choices in sections 2 to 5.
- **Assumption, not confirmed:** nothing about machines, sensors, PLCs, headcount, current software or hosting is assumed. These are open questions in section 7.

## 2. Three-Phase Implementation Plan

The phases follow the physical flow of material and the data it depends on: Phase 1 builds the controlled core (master data, stock, production, quality), Phase 2 adds planning, commercial and money, Phase 3 adds support functions, executive layer and AI. Each phase ends with a system the factory can use in daily work.

### Why this distribution

- **Traceability and GMP come first** because every later module (costing, MRP, AI) depends on trustworthy lot, batch and quality data.
- **Finance and MRP come second** because they need real stock movements, approved formulas and batch data to be correct, not estimated.
- **AI comes last** because models need months of clean data. Phase 1 and 2 are designed to capture the data AI will need (see section 6).
- Modules are not split equally. Phase 1 is the heaviest because it holds the regulated core.

### Phase 1 – Controlled Core: Formula, Stock, Production, Quality

**Objective:** digitize the regulated manufacturing flow with full lot traceability and batch release.

**Modules and features**

- **Platform foundation:** users, roles and permissions (RBAC), organization and warehouses, audit log, electronic approvals and signatures, document storage, notifications, multi-language and multi-currency readiness.
- **R&D / PLM (core):** ingredient database with INCI names and specifications, formulations with percentages, versioning, approval workflow, lab-to-industrial scaling, basic raw material cost, packaging specifications.
- **Inventory and WMS:** items, lots, locations, quarantine/approved/rejected status, barcode/QR labels, FEFO allocation, movements, min/max levels, stock reconciliation.
- **Procurement (core):** supplier database, requisitions, purchase orders, goods receipt into quarantine, supplier qualification record.
- **Production (MES core):** production orders, eBMR, eBPR, dispensing with weighing verification, machine/line allocation, process parameters, yields, losses and waste, batch tracking.
- **QC / LIMS:** specifications, raw/packaging/in-process/finished testing, microbiology, water monitoring, COA, equipment records, result history.
- **QA / QMS (core):** SOP and document control, deviations, CAPA, change control, batch review and release, training records, complaints, recall initiation, audit trail.
- **Basic reports:** stock, batch status, traceability (forward and backward).

**Deliverables:** a working receipt-to-release flow; electronic batch records; a one-click traceability report; stock that excludes quarantined and rejected material.

**Dependencies:** none external; internal order is in section 3.

**Business value:** GMP discipline, audit readiness, elimination of paper batch records and stock mismatches.

**Major challenges:** modeling formulas and lots correctly from day one; migrating items, formulas and opening stock; regulated e-approval design (to be validated against ISO 22716 by the factory's QA); barcode hardware and scale integration.

### Phase 2 – Planning, Commercial and Money: MRP, Sales, Costing, Finance

**Objective:** connect operations to demand and money so purchasing, production and pricing are planned and profitable.

**Modules and features**

- **MRP:** requirements from confirmed orders and forecasts, approved formulas, stock, open POs, lead times, minimum stock; suggested purchase requisitions and production orders; finite-capacity checks as data allows.
- **Sales and CRM:** customers (domestic, export, private-label, contract), quotations, sales orders, pricing and discounts, customer-specific requirements, payment tracking, communication history.
- **Procurement (complete):** quotations, price comparison, approval workflows, delivery tracking, price history, supplier performance evaluation.
- **Finance and industrial costing:** standard and actual batch cost (materials, packaging, labor, energy, overhead), accounts payable and receivable, multi-currency, cash flow monitoring, budgeting, financial reports.
- **Export and regulatory:** export orders, commercial invoices, packing lists, shipment documents, country requirements, product registrations and certificates, shipment tracking.
- **Delivery and finished goods:** pick, pack, dispatch against released batches only.
- **R&D extensions:** stability testing records, alternative ingredient comparison, formula cost comparison.
- **Reporting layer:** management reports and data warehouse views (the foundation of the CEO dashboard and AI).

**Deliverables:** order-to-cash and procure-to-pay cycles; actual versus standard cost per batch; MRP shortage and purchase suggestions; export document sets.

**Dependencies:** Phase 1 stock, batch, formula and supplier data; decision on whether Laravel ERP replaces or exports to the factory's existing accounting system (open question).

**Business value:** fewer shortages and excess stock, real product and customer profitability, controlled cash.

**Major challenges:** costing accuracy (overhead allocation rules need finance sign-off); MRP quality depends on lead times and BOM accuracy; accounting depth (local tax and statutory rules) to be defined with the client.

### Phase 3 – Support Functions, Executive Layer and AI

**Objective:** complete the remaining operations, give management one view, and add AI where data is now sufficient.

**Modules and features**

- **HRM:** employee records, departments, attendance, shifts, leave, GMP qualification matrix, performance, workforce planning. (Training records already exist from Phase 1 QA.)
- **CMMS:** equipment register, preventive and corrective maintenance, calibration plans and certificates, breakdowns, spare parts, maintenance cost, availability.
- **CEO Command Center:** all executive KPIs, responsive on phone, tablet and computer.
- **AI Executive Assistant:** natural-language questions answered from permission-checked ERP queries.
- **AI models, in priority order:** conventional analytics first (inventory, cost anomaly, lab trends), then forecasting (demand, sales, cash), then machine-data models (filling anomaly) and computer vision (labeling), then predictive maintenance.

**Deliverables:** complete platform; executive dashboard; first production AI use cases with measured results; human-approval workflow for AI recommendations.

**Dependencies:** at least 6 to 12 months of Phase 1 and 2 data for forecasting; machine data access and cameras for the filling and labeling projects (unknown today).

**Business value:** management visibility, maintenance discipline, measurable waste and downtime reduction.

**Major challenges:** machine connectivity and sensors; model validation; data privacy and permission enforcement in the assistant; avoiding unexplainable AI recommendations.

### Early-win note (not a fourth phase)

Filling-weight logging and label-defect logging (manual or semi-automatic) should be captured from Phase 1 as simple eBMR/in-process fields. This starts building the datasets the AI projects need without any AI in Phases 1 and 2.

## 3. Module Dependency Analysis

Inventory, formulas and quality status are the base everything else stands on, so they are built first and shared by every later module.

### Dependency table

| Module | Depends on | Feeds | Phase |
| --- | --- | --- | --- |
| Platform (users, RBAC, audit, approvals, files) | None | All modules | 1 |
| Master data (items, units, warehouses, suppliers) | Platform | All modules | 1 |
| R&D / formulation | Master data, approvals | Production, MRP, costing | 1 (core), 2 (extensions) |
| Inventory / WMS | Master data | Production, QC, sales, finance, MRP | 1 |
| Procurement | Master data, inventory | Finance, MRP | 1 (core), 2 (full) |
| QC / LIMS | Inventory (lots), specifications | QA release, inventory status, COA | 1 |
| QA / QMS | Platform, QC, production | Batch release, inventory status | 1 |
| Production / MES | Approved formula, inventory, QC | Costing, QA, finished goods | 1 |
| MRP | Formulas, inventory, sales orders, POs, lead times | Procurement, production planning | 2 |
| Sales / CRM | Master data, finished goods stock | Finance, export, MRP | 2 |
| Finance and costing | Inventory movements, production actuals, procurement, sales | Dashboard, AI | 2 |
| Export and regulatory | Sales, finished goods, QA documents | Finance | 2 |
| CMMS | Equipment records, spare parts stock | Production (availability), AI | 3 |
| HRM | Platform users | QA training matrix, production staffing | 3 |
| CEO dashboard | All operational modules | Executives | 3 |
| AI layer | Clean history from all modules, RBAC | Dashboard, planners | 3 |

### Recommended build sequence

1. Platform foundation: authentication, RBAC, audit log, approval engine, file storage.
2. Master data: items, units of measure, suppliers, warehouses and locations.
3. Inventory ledger and lot model (the single source of stock truth).
4. Formulation and ingredient database with approval workflow.
5. Procurement core and goods receipt into quarantine.
6. QC specifications and test recording for incoming materials; status release back to inventory.
7. Production orders, dispensing, eBMR/eBPR, yield and loss.
8. In-process and finished-product QC, then QA batch review and release.
9. Finished goods storage; basic traceability report.
10. Phase 2: sales orders and dispatch, costing, MRP, finance, export.
11. Phase 3: CMMS, HRM, executive dashboard, then AI.

### Critical integration points

- **Goods receipt to QC:** receipt creates quarantined lots and QC tasks; QC result changes lot status through an auditable event, never by editing stock.
- **Production to inventory:** each dispensing step creates a consume transaction against a specific lot; no free-text consumption.
- **Production to QC and QA:** in-process results can hold a batch; only QA release makes finished goods available to sales.
- **Inventory to finance:** every stock movement carries a value so costing and the general ledger stay consistent with stock.
- **Sales to MRP:** confirmed orders create demand; MRP outputs are suggestions that need human approval.
- **CMMS to production:** an out-of-calibration or broken machine blocks allocation to production orders.
- **HRM / training to QA:** untrained or unqualified operators cannot sign GMP steps (recommended control, to confirm with the factory).
- **All modules to AI layer:** AI reads through the same permission-checked service layer as users, never directly from tables.

## 4. Recommended Laravel Architecture

Build a modular monolith on Laravel 13 with PostgreSQL, Redis queues and a separate Python service reserved for AI workloads. This gives one deployable system with strict internal module boundaries, which is the right fit for a single-factory ERP with a small to mid-size team.

### Why a modular monolith, not microservices

- ERP transactions cross modules constantly (receive, test, release, consume, cost). One database transaction keeps stock and finance consistent; distributed services would need sagas and eventual consistency for rules that must be exact.
- One team, one factory, one deployment: microservices would add operational overhead without a scaling need we can currently justify.
- Strict module boundaries (below) keep a later split possible. The only separate service we recommend is the AI/ML service, because Python tooling and GPU/edge needs genuinely differ.

### Backend

| Area | Recommendation | Reason |
| --- | --- | --- |
| Framework | Laravel 13 on PHP 8.3 to 8.5 | Laravel 13 is the current major release, with bug fixes until Q3 2027 and security fixes until Q1 2028. Laravel has no LTS line, so plan a routine upgrade each year (Laravel 14 expected in Q1 2027). |
| Structure | Domain modules inside one app, for example Modules/Inventory, Modules/Production, Modules/Quality | Own package-free structure avoids dependence on a third-party module package; confirm if the team prefers one. |
| Layering | Controllers, Form Requests, Actions/Services, Policies, Eloquent models, Events | Business rules live in actions and services, not controllers or models. |
| Module boundaries | Modules call each other only through public service contracts and domain events | Prevents tangled dependencies; enforced by architecture tests in CI. |
| Money and quantity | Decimal-safe types, explicit units of measure and conversion tables | Formulas, stock and costs fail silently with floats. |
| Testing | Pest, feature tests per workflow, architecture tests, static analysis | Needed for regulated, long-lived software. |

### Database

- **PostgreSQL** is recommended: strong transactional guarantees, row-level locking for stock, partial indexes, JSONB for flexible test parameters, window functions and materialized views for reporting, and good partitioning for large movement and audit tables.
- MySQL 8 is acceptable if the factory's hosting or team standard requires it; this is a decision point to confirm.
- **Inventory is an append-only ledger.** Stock balances are derived from immutable movement rows (with cached balances updated in the same transaction). Corrections are reversing movements, never edits.
- Foreign keys, check constraints and unique constraints enforce business rules in the database, not only in PHP.
- Soft-delete only for master data; transactional and quality records are never deleted.

### Authentication and authorization

- Laravel authentication with sessions for the web app and Sanctum tokens for APIs and the mobile/tablet client; mandatory MFA for approvers and administrators.
- **RBAC** with roles and fine-grained permissions (for example batch.release, formula.approve) plus Laravel Policies. A well-known package such as Spatie Laravel-Permission is a reasonable base; the final choice is ours to confirm.
- **Data scope rules** on top of roles (by warehouse, department, plant) for what each user can see.
- **Electronic signature:** re-authentication at the moment of approval, with meaning of signature (author, reviewer, approver), timestamp, and a record locked after signing. Segregation of duties (for example the QA releaser cannot be the batch producer).

### API strategy

- Versioned REST API (/api/v1) with JSON Resources and OpenAPI documentation, used by the web app, tablets and future machine or partner integrations.
- The web interface can call the same application services directly; the API is not a second implementation.
- Idempotency keys for machine and scanner submissions to avoid duplicate movements.
- Webhooks and import endpoints for future integrations (scales, label printers, accounting, PLC gateways), none of which are assumed today.

### Frontend (confirm with the team)

- Recommended: Inertia with Vue (or React) and Tailwind for rich screens such as eBMR, dashboards and barcode workflows, delivered as a responsive PWA for tablets and phones.
- Alternative: Livewire for faster CRUD-heavy delivery if the team is PHP-only. Both work on Laravel 13; the choice depends on team skills, which we do not know yet.
- Language and right-to-left needs (for example Arabic and English) must be confirmed; build with full localization from day one.

### Background jobs, caching and notifications

| Area | Recommendation |
| --- | --- |
| Queues | Redis queues with Laravel Horizon for MRP runs, report exports, label printing, imports, notifications and AI jobs; failed-job handling visible to admins |
| Scheduler | Laravel scheduler for expiry alerts, calibration due dates, overdue CAPA, reorder checks, nightly KPI snapshots |
| Caching | Redis for configuration, permissions and dashboards. Never cache values used to validate a stock transaction. |
| Notifications | In-app and email first; real-time updates via Laravel Reverb or equivalent; SMS/WhatsApp only if the client wants it and a provider is chosen |

### Audit logging

- **Data audit:** every create, update and delete on regulated entities records user, time, old value, new value and reason, in an append-only table with restricted database privileges.
- **Business event log:** release, rejection, approval, status changes and AI recommendations accepted or rejected.
- Optional tamper-evidence via hash chaining of audit rows; to be decided with QA.
- Audit data is retained per the factory's record-retention policy (an open question).

### File and document management

- S3-compatible object storage (cloud or a local MinIO server) for SOPs, COAs, certificates, invoices, photos and specifications; metadata and version history in the database.
- Controlled documents have states (draft, review, effective, obsolete), version numbers and read-and-understood tracking.
- Virus scanning on upload, private storage with signed temporary links.

### Reporting architecture

- Phase 1: operational reports from optimized database queries and views.
- Phase 2: a read replica and materialized views for management and financial reports; heavy exports run as queued jobs.
- Phase 3: curated reporting tables (a lightweight data mart) feeding the CEO dashboard and AI. A separate BI tool is optional, not required.

### AI integration approach

- Laravel stays the **system of record**. AI services never write to ERP tables directly; they return predictions or recommendations that users approve.
- A **Python ML service** (forecasting, anomaly detection, computer vision) communicates through queues and an internal API. Edge inference near the line for vision, if cameras are installed.
- Features and predictions are stored in ERP tables with model version, inputs and explanation, so every recommendation is traceable.
- **AI Executive Assistant:** a language model calls a fixed catalog of permission-checked, read-only query tools exposed by Laravel (for example, production output for a date range). It does not generate free SQL against the database. The user's permissions are applied to each tool call, and each question, tool call and answer is logged. Whether the language model runs in the cloud or on local servers is a data-privacy decision for the owner.
- Quality and release decisions stay with authorized people; AI output is advisory.

### Hosting and operations

- Containerized deployment (Docker) with environments for development, staging, validation and production; automated backups with tested restores; monitoring and error tracking.
- On-premise server, local data center or cloud hosting is open; internet reliability at the factory floor affects the choice (offline tolerance for scanning is a requirement to evaluate).
- Security: encryption in transit and at rest, least privilege, periodic access reviews, penetration test before go-live.

### Sources

- [Laravel release notes and support policy](https://laravel.com/docs/12.x/releases) (version table, as of 2026-11-01 search)
- [Independent summary of Laravel 13 release](https://benjamincrozat.com/laravel-versions)

## 5. Database Design Strategy

Three structures carry the whole system: an item and lot model that identifies every material and product, an append-only inventory ledger that records every movement, and a batch record that ties consumed lots to produced lots. Everything else (quality, finance, sales) references those.

### Main entities and relationships

| Area | Key entities | Main relationships |
| --- | --- | --- |
| Master data | Item (raw material, packaging, semi-finished, finished), unit of measure, conversion, warehouse, location, supplier, customer, currency | An item has units, specifications and approved suppliers; locations belong to warehouses |
| Products and formulations | Product, formulation, formulation version, formulation line (ingredient, percentage, function), ingredient (INCI name, specification), alternative ingredient, stability study, packaging specification | A product has many formulation versions; one version is approved and effective at a time; each line points to an ingredient item |
| Suppliers and procurement | Supplier, supplier qualification, requisition, quotation, purchase order and lines, goods receipt and lines, supplier price history, supplier evaluation | Goods receipt lines reference PO lines and create lots |
| Inventory | Lot, inventory ledger entry, stock balance, reservation, stock count, quarantine/approved/rejected status history | A lot belongs to one item and one origin (receipt or production batch); the ledger references lot, location, quantity, unit, value, source document |
| Production | Production order, batch, eBMR and eBPR with steps, dispensing record, consumption (ledger link), process parameter record, yield and loss record, line and machine allocation | Production order references an approved formulation version; a batch consumes specific lots and creates output lots |
| Quality control | Specification, test method, test plan, sample, test result, certificate of analysis, equipment used, water monitoring points | A sample belongs to a lot; results are compared with the specification version in force |
| Quality assurance | Controlled document and version, deviation, CAPA, change control, audit, complaint, recall, batch release decision, training record, electronic signature | Deviations and complaints can link to lots and batches; release decision links to a batch |
| Sales and export | Customer, quotation, sales order and lines, delivery, export order, commercial invoice, packing list, shipment, product registration | Delivery lines pick released finished lots; invoices reference deliveries |
| Finance | Chart of accounts, journal entry, payable, receivable, payment, cost rate, standard cost, batch actual cost, budget | Ledger movements and batch costs post to journals; payables reference receipts; receivables reference deliveries |
| Equipment and maintenance | Equipment, calibration plan and certificate, maintenance plan and work order, breakdown, spare part | Production and QC records reference the equipment they used |
| Platform | User, role, permission, approval request, audit log, attachment, notification | Every transactional record has creator, timestamps and audit history |

### Core rules the schema must enforce

1. No consumption without a ledger entry. A production consumption row cannot exist without a ledger entry linked to a specific lot.
2. Only lots with approved status are available to production and sales. Quarantined and rejected lots are excluded from available stock by query design, not by convention.
3. Production order requires an approved, effective formulation version, which is frozen into the batch record at start.
4. Batch release requires completed QC results within specification and an authorized QA signature; finished goods become sellable only after it.
5. Ledger entries and signed records are immutable; corrections are new, linked entries.
6. Every ledger entry carries a value, so stock valuation and finance reconcile.
7. Units are stored in a base unit with explicit conversions; formula percentages must sum to a defined total.

### Traceability chain

Backward trace (from a customer delivery to the supplier) and forward trace (from a supplier lot to every customer) use the same chain of links:

1. **Supplier and purchase order:** supplier lot number, PO line.
2. **Goods receipt:** creates an internal lot in quarantine with receipt date, expiry and received quantity.
3. **QC release:** samples, test results, COA and status change on the lot.
4. **Storage and movement:** each ledger entry records warehouse, location and document.
5. **Dispensing:** weighed quantities per lot for each formula line, with scale and operator.
6. **Batch manufacturing:** eBMR steps, parameters, equipment and operators, linked to the formulation version.
7. **Semi-finished and packaging:** intermediate lots, packaging component lots and filling/labeling records.
8. **Finished lot and QC/QA release:** finished product tests, deviations and release signature.
9. **Delivery:** sales order, picked lot, customer, shipment and invoice.

With this chain, the answer to "which customers received products made with raw material lot X" is a recursive query over the ledger and batch consumption links, and a recall can be scoped in minutes instead of days.

### Design practices

- UUID public identifiers plus numeric keys internally; human-readable document numbers per module (PO, batch, lot).
- Versioned master documents (formulations, specifications, SOPs) where old versions are never overwritten.
- Partitioning for ledger and audit tables by period once volumes justify it.
- A deliberate reporting layer (views, later a data mart) so analytics and AI never query operational tables ad hoc.
- Detailed schema design (columns, indexes, migrations) starts only after this plan is approved and the discovery questions are answered.

## 6. AI Feasibility Analysis

Most proposed AI ideas are feasible but only after data exists; two (filling and labeling) depend on hardware access we know nothing about yet. Several valuable "AI" results are really conventional analytics and should be built that way. Priority: **A** = start in Phase 3 with ERP data alone, **B** = Phase 3 after enough history, **C** = depends on hardware or a pilot first.

### Summary

| Opportunity | Business problem | Method | Needs sensors/cameras | Needs history | Priority | Main risk |
| --- | --- | --- | --- | --- | --- | --- |
| Inventory stockout and excess detection | Shortages and tied-up cash | Conventional analytics (reorder rules, consumption trends), later forecasting | No | Some months of movements | A | Poor lead-time and BOM data |
| Cost anomaly detection | Unnoticed cost or yield drift | Statistical rules and anomaly detection | No | Batch cost history | A | Costing rules not stable |
| Supplier price analysis | Price increases unnoticed | Conventional analytics | No | PO and price history | A | Short history |
| Laboratory trend analysis and quality anomaly detection | Drifting results, out-of-trend tests | Statistical process control, then ML | No (lab data) | Test result history per spec | A | Inconsistent test recording |
| Demand and sales forecasting | Over- or under-production | Time-series forecasting | No | Typically 12 to 24 months of sales | B | Seasonality, few customers, private-label lumpiness |
| Cash flow and profitability forecasting | Liquidity planning | Forecasting on orders, receivables, costs | No | Finance history | B | Data completeness |
| Production optimization, bottleneck and downtime analysis | Idle lines, delays | Analytics first; scheduling optimization later | Machine states helpful, not required | Production and downtime logs | B | Downtime not recorded accurately |
| Production capacity forecasting | Unrealistic schedules | Analytics on rates and yields | No | Production history | B | Capacity not defined per line/product |
| Formulation cost optimization and ingredient substitution | Cost and supply risk | Rule-based comparison; optimization under constraints | No | Formulation, price, stability and QC data | B | Formula changes require R&D and regulatory approval; AI only suggests |
| Formulation risk analysis | Stability and compatibility problems | Rules from historical stability data | No | Stability results | C | Small datasets; cannot replace expert judgment |
| Filling anomaly detection and prediction | Under- or over-filled containers | Statistical process control first; ML if machine data exists | Depends: check-weigher or filler data needed | Filling measurements | C | We do not know what machine data is available |
| Label placement inspection | Misplaced labels, manual adjustment | Computer vision (deep learning) on images | Yes: camera, lighting, trigger, possibly reject device | Labeled images of good and bad labels | C | Hardware, line speed, lighting, reject integration |
| Predictive maintenance | Breakdowns | Failure trend analysis, later ML | Sensor data only for advanced models | Maintenance history, ideally sensor logs | C | Few failure events to learn from |
| Customer segmentation and profitability | Where to focus sales | Conventional analytics, clustering | No | Order and cost history | B | Few customers may make this simple reporting |
| AI Executive Assistant | Fast access to answers | Language model plus permission-checked query tools | No | ERP data in good quality | B | Wrong answers if data is poor; permission leaks; data privacy |

### The two named factory problems in detail

**Filling machine variation (under- and over-fill).**

- *Problem:* filled quantity outside tolerance; product giveaway or non-conformity.
- *Required data:* weight or volume per container (check-weigher, manual sampling, or machine output), machine ID, speed, product, viscosity or temperature, nozzle, time, operator.
- *Output:* alerts on trends toward limits; machine or product combinations associated with deviation; later, a prediction of drift before it happens.
- *Step 1 (no AI):* record filling checks in the eBMR/eBPR from Phase 1 and chart them with control limits. This reveals whether the issue is a machine, a product, or an operator practice.
- *Step 2:* if automated measurement exists, add anomaly detection. If it does not, ML adds little; a check-weigher may be the right investment.
- *Method:* statistical process control, then ML only if machine data supports it.

**Labeling misplacement.**

- *Problem:* labels crooked or in the wrong position, causing manual correction and lost time.
- *Required data:* images of finished containers at the labeling station, with good/defect labels and defect classes.
- *Output:* pass/fail, defect type, position offset, alert or reject signal.
- *Method:* computer vision; classical image processing may suffice for position and angle, deep learning for varied products and surfaces.
- *Requires:* camera, lighting, trigger and a way to stop or reject; this is a hardware project as much as a software one. Root-cause review of the labeling machine (setup, label stock, sensor calibration) should come first, since some causes are mechanical.
- *Recommendation:* run a small pilot with one camera on one line before committing.

### Rules for every AI feature

- Each model has an owner, a measurable target (for example, fewer shortages, lower giveaway, fewer manual label corrections) and a baseline measured before launch.
- Predictions are stored with model version and inputs, and are explained in business terms.
- Quality and release decisions are never automated; AI can flag, a person decides.
- Models are retrained and monitored for drift; wrong or unused models are retired.
- The assistant never answers from memory: if the data tool returns nothing, it says so.
- The assistant honors the user's role and data scope on every question.

## 7. Missing Information and Client Questions

We know the business scope and two machine problems; we do not yet know the factory's size, machines, data, software, rules or hosting. Nothing in the tables below is assumed to exist until the client answers.

### Status of information

| Status | Items |
| --- | --- |
| Confirmed by the client | Laravel foundation; 12 business modules; three phases; AI layer as future direction; executive dashboard; filling and labeling problems exist; human control of critical quality decisions; ISO 22716 alignment goal |
| Our recommendations (to approve) | Modular monolith, PostgreSQL, phase contents, inventory ledger design, AI sequencing, frontend choice |
| Unknown (must ask) | Everything in the questions below |
| Not assumed | Specific machines, sensors, PLCs, cameras, barcode hardware, existing software, accounting system, headcount |

### Questions for Beauty Italy Factory

**Organization and scale**

- How many employees, by department, and how many will be ERP users? Any multiple sites or warehouses?
- Production shifts and working days?
- Who are the decision makers and the process owner for each department?

**Products and formulations**

- How many products, formulas and SKUs, and in what form do formulas exist today (Excel, paper, in someone's head)?
- Are formulas versioned? Who approves a formula now?
- Product types (creams, shampoos, perfumes, color cosmetics) and batch size ranges?
- Do you manufacture private-label and contract products with client-owned formulas or confidential specifications?

**Production**

- Number of production lines, filling machines (type and brand), labeling machines, mixers/tanks and packaging lines?
- How are batch records kept today? Is there a paper BMR?
- Do machines have PLCs, data outputs or check-weighers? Are cameras installed or possible?
- What is the typical filling tolerance, and how is it checked today? How often does the labeling issue occur?

**Inventory and warehouse**

- Current inventory process and tools; number of items and locations; current lot and barcode practices.
- Which barcode scanners, label printers and scales exist? Is the warehouse network reliable (Wi-Fi coverage)?
- Is opening stock reliable and available for migration?

**Quality**

- Current QC test list, specifications, methods and lab equipment; how results are recorded.
- Existing SOPs, deviation and CAPA procedures; who releases batches?
- Are there certification targets (ISO 22716, ISO 9001, export-market requirements)? Who performs the compliance review?

**Procurement, sales and export**

- Number of suppliers and customers; local versus imported materials; lead times.
- Which countries do you export to and which registrations (for example product notification requirements) apply?
- Pricing method; customer credit terms; commissions or agents.

**Finance**

- Existing accounting software; should the ERP replace it, integrate with it, or only feed it?
- Chart of accounts, tax and statutory reporting rules, currencies, costing method preferences (standard, actual, overhead allocation).
- Who needs financial approvals and at what limits?

**HR and maintenance**

- Current attendance system (biometric devices, paper), payroll handled where?
- Equipment count, current maintenance and calibration records, and spare parts practice.

**Users, roles and security**

- User roles and approval authorities per department; segregation-of-duties expectations.
- Which languages must the interface support (including right-to-left)?
- Who can see costs and profit data?

**Technology and deployment**

- Hosting preference (on-premise, local data center, cloud); internet reliability; IT staff available.
- Devices on the floor (tablets, kiosks, phones) and printers.
- Data privacy expectations, including whether data may be sent to external AI providers.
- Backup, retention and disaster recovery expectations.

**Project and data**

- Existing Excel files and software to import (stock lists, formulas, supplier lists, customer lists).
- Budget range, target dates, internal project owner, and availability of key users for workshops and testing.
- Is a validation exercise (formal GMP software validation) expected, and by whom?

## 8. Development Roadmap

The path runs from discovery through three delivery phases, each ending with go-live and stabilization, with AI work starting only once data exists. Durations depend on team size and the answers in section 7, so none are committed here; they should be estimated after discovery.

### Stages and gates

| Stage | Main activities | Output | Gate to continue |
| --- | --- | --- | --- |
| 0. Discovery and analysis | Workshops with each department; observe real workflows; collect Excel files, forms and SOPs; answer section 7 | Process maps, requirements, data inventory, risk list | Factory owner approves scope and this plan |
| 1. Architecture and design | Confirm architecture; detailed domain model and schema; roles and approval matrix; UI prototypes for key flows (receipt, dispensing, eBMR, QC, release); validation approach agreed with QA | Technical design, prototypes, backlog | Key users sign off prototypes |
| 2. Phase 1 build and test | Foundation, master data, inventory, R&D, procurement core, QC, QA, production; data migration scripts; scanner and label printing setup | Working system in staging | User acceptance tests and traceability test pass (mock recall) |
| 3. Phase 1 go-live | Training, pilot on one line or product group, parallel run with current records, then full cutover; hypercare | Production use; baseline data for AI | Stable operation and QA acceptance |
| 4. Phase 2 build and go-live | MRP, sales, full procurement, costing, finance, export; cost rules agreed with finance | Order-to-cash, procure-to-pay, costed batches | Finance reconciliation and UAT sign-off |
| 5. Phase 3 build and go-live | CMMS, HRM, CEO dashboard; AI foundation (data layer, model service, assistant skeleton) | Complete platform and dashboard | Dashboard KPIs verified against source data |
| 6. AI delivery | Measure baselines, pilot A-priority analytics, then forecasting, then machine/vision pilots | Production AI with measured results | Each model meets agreed accuracy and business target before rollout |
| 7. Operate and improve | Support, upgrades (Laravel yearly), backups tested, security reviews, model monitoring | Stable service | Ongoing |

### Practices throughout

- **Incremental delivery:** every few weeks a demonstrable, testable slice inside each phase, not a single large handover.
- **Testing:** automated tests for business rules (stock, status, approvals), user acceptance tests on real scenarios, performance and security testing before each go-live.
- **Validation:** keep requirement, test and approval evidence for regulated workflows; the factory's QA decides the formal validation level and any compliance claim.
- **Data migration:** clean and load items, formulas, suppliers, customers and opening stock early, with reconciliation before cutover; this is usually the longest hidden task.
- **Change management:** train by role, name department champions, and plan parallel run to avoid people returning to paper.
- **Risk control:** monthly review of scope, data readiness, machine access and budget; a decision log for assumptions turned into confirmed requirements.

### Immediate next steps

1. Owner reviews this document and approves, corrects or rejects the phase distribution.
2. We send the section 7 question list and schedule department workshops.
3. We collect sample files (formulas, stock lists, a paper batch record, QC forms).
4. After answers, we update this document, then begin detailed design. No code starts before your approval.
