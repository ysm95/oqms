# QMS Permit Issuing / Control of Work Scope

## Purpose

Permit Issuing is added to the QMS scope as a Safety module for controlling high-risk, non-routine, maintenance, contractor, and operational work before it starts.

The module should support safe work authorization, risk review, isolation controls, field execution, monitoring, suspension, extension, handover, and closeout.

## Scope Position

Permit Issuing belongs under:

```text
Safety > Permits
```

It must remain part of the single QMS product shell. It should not become a separate application or create a nested navigation system.

## Core Permit Types

Initial permit types:

- General Work Permit
- Hot Work Permit
- Confined Space Entry Permit
- Electrical Work Permit
- Isolation / LOTO Permit
- Work at Height Permit
- Excavation Permit
- Lifting Operation Permit
- Contractor Work Permit
- Maintenance Work Permit

Permit types must be configurable by authorized administrators.

## Permit Lifecycle

The permit lifecycle should include:

1. Draft
2. Risk Assessment
3. Controls and Isolation
4. Review
5. Approved
6. Issued
7. Active
8. Suspended
9. Extended
10. Handover
11. Closed
12. Cancelled

## Guided Permit Pages

Permit creation should use a guided page-based form:

1. Work Information
   - permit type
   - requested by
   - department
   - contractor
   - area
   - unit
   - asset or equipment
   - work description
   - planned start and end

2. Hazards and Risk
   - hazard identification
   - job safety analysis
   - risk rating before controls
   - required control measures
   - risk rating after controls

3. Controls and Isolation
   - energy sources
   - LOTO points
   - isolation verification
   - gas testing requirement
   - fire watch
   - standby person
   - barricading
   - PPE
   - emergency arrangements

4. Documents and Competency
   - linked procedures
   - method statement
   - drawings or diagrams
   - required training
   - contractor approval
   - competency verification

5. Review and Approval
   - area authority review
   - HSE review
   - operations review
   - maintenance review
   - contractor supervisor review
   - final issuer approval

6. Issue and Field Execution
   - issue time
   - permit validity
   - QR / permit reference
   - field acknowledgement
   - active permit checks
   - extension request
   - suspension

7. Closeout
   - work complete
   - worksite restored
   - isolations removed
   - final inspection
   - observations or actions created if needed

## Permit Board

The module should include a Permit Board with saved views:

- Draft
- Pending Review
- Ready to Issue
- Active
- Expiring Soon
- Suspended
- Closed
- Cancelled

The board should support search, filters, priority, area, contractor, permit type, owner, approver, due time, and status.

## Integrations With Existing QMS Modules

Permit Issuing should connect to:

- Observations: create an observation from unsafe permit conditions
- Incidents: link permit failures to accepted incidents
- Actions: create follow-up actions from reviews, field checks, or closeout
- Risk Register: link high-risk work and reusable controls
- Training: verify competency and required training
- Documents: link procedures, drawings, method statements, and controlled forms
- Contractors / Suppliers: verify contractor approval and performance
- Audits / Inspections: audit permit quality and field compliance
- Notifications: alert approvers, issuers, area owners, and workers
- Audit Trail: record every review, signature, status change, extension, suspension, and closeout

## Roles

Initial roles:

- Requester
- Permit Applicant
- Contractor Supervisor
- Area Authority
- HSE Reviewer
- Operations Reviewer
- Maintenance Reviewer
- Isolation Authority
- Gas Tester
- Fire Watch
- Permit Issuer
- Permit Approver
- Permit Closer
- Safety Admin
- System Admin

Each role must be scoped by site, area, department, contractor, and assigned work.

## Rules And Controls

The module should enforce:

- No issuing without required approvals
- No active permit outside valid date and time
- No closeout until required isolations and final checks are complete
- No extension without authorization
- No conflicting simultaneous work without review
- Mandatory controls based on permit type and risk
- Mandatory competency checks for defined work types
- Full audit trail for regulated evidence

## Mobile And Field Use

The mobile experience should support:

- permit lookup by reference or QR code
- field acknowledgement
- active permit status
- checklist completion
- photo evidence
- stop / suspend request
- extension request
- closeout confirmation

Mobile may cache drafts and assigned field checks, but the Laravel backend remains the authoritative source of truth.

## AI Boundary

AI may assist with permit drafting or missing-control suggestions only after approved secured provider governance is enabled.

AI must not issue, approve, extend, suspend, or close permits. Human approval is mandatory.

AI output must show source traceability and remain auditable.

## Out Of Scope For First Release

The first release should not include:

- automatic permit approval
- independent external permit database
- uncontrolled AI authorization
- replacing legal procedures without approval
- direct industrial control-system commands
- copied third-party vendor screen designs

## First Implementation Backlog

Recommended first backlog:

1. Permit data model and numbering
2. Permit type configuration
3. Guided permit request form
4. Approval workflow
5. Permit board and saved views
6. Issue / suspend / extend / close actions
7. Audit trail and notification events
8. Link permits to actions, documents, training, contractors, observations, and risks
9. Feature tests for lifecycle and authorization boundaries
10. Mobile-friendly field view
