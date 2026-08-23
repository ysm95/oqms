# QMS Implementation Status

This Laravel QMS implementation is the active product baseline for qms.ysaidea.com. The local working tree is the implementation source of truth.

Implemented in the current application:

- One coherent QMS product shell with role-aware top navigation, global search, create menu, Help, notifications, My Work, and progressive module disclosure
- Observation workflow for Unsafe Act and Unsafe Condition reporting, HSE review and Action Tracker follow-up
- Reporter-only experience with Home, Report, My Reports, Notifications, and Help; reporter users do not see internal workflow, investigation, CAPA, internal comments, or administration
- Central Reports workspace with report screening, comments, actions, attachments, related records, history, print, accept, and reject
- Accepted reports create exactly one incident transactionally; rejected reports remain reporting records only
- Independent incident numbering and reporting numbering
- Occurrence, incident, action, investigation, risk, document, training, audit, compliance, objective, supplier, public report, admin, report designer, notification designer, workflow, and form designer foundations
- Field assurance separation for audit, inspection, finding, NCR, CAPA, compliance change, safety promotion, and feedback records
- Versioned standards registry and taxonomy registry storing internal mappings only; no licensed standards text is copied into the system
- Safety promotion / lessons learned records separated from confidential safety reports
- User feedback separated from safety reporting
- Controlled AI governance records; AI remains blocked until a paid secured provider, permission model, source traceability, and human approval are enabled
- Database migrations, rollback methods, seed data, and feature tests for the implemented business boundaries
- Seeded demo users:
  - `admin@qms.test` / `password`
  - `yahya.alnaaimi@qms.test` / `Yahya@2026`
  - `mazin.alfarsi@qms.test` / `Mazin@2026`
  - `aisha.albalushi@qms.test` / `Dummy@2026`
  - `omar.alharthy@qms.test` / `Dummy@2026`
- Hostinger VPS deployment script template in `deploy/hostinger_publish_qms.sh`
- Production runbook with `APP_DEBUG=false`, backup, deployment, queue worker, scheduler, and rollback requirements

Remaining production dependencies:

- Configure production database credentials on the VPS.
- Run online Composer and npm audits from an environment with registry access.
- Configure supervised queue workers and the scheduler on the VPS.
- Connect real mail, SSO/Entra, integration APIs, backup storage, and approved secured AI provider credentials.
- Complete UAT with real controlled procedures and organization-specific taxonomies before declaring regulated production go-live.
