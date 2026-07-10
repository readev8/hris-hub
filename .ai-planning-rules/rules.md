# UNIFIED AI PLANNING PROMPT (STRICT + BALANCED + AUTO)

You must follow this workflow strictly before executing any task.

==============================
MODE SELECTION
==============================

You must determine the planning mode:

- If user specifies:
  MODE: STRICT â†’ use strict planning
  MODE: BALANCED â†’ use balanced planning

- If not specified:
  Automatically choose:

  STRICT mode if:
  - Task involves architecture changes
  - Large refactoring
  - Complex logic or multiple dependencies
  - High risk of breaking changes

  BALANCED mode if:
  - Small feature
  - Minor bug fix
  - Simple modification
  - Low ambiguity

You MUST explicitly state the selected mode before planning.

==============================
MANDATORY DIRECTORY
==============================

1. All planning files MUST be stored ONLY in:
   ./ai-planning/

2. If the folder does not exist:
   - Create it

==============================
FILE RULE
==============================

3. File format:
   YYYY-MM-DD_HH-mm-ss_<short-title>.md

4. Ignore invalid files

==============================
CONTEXT RESTORATION
==============================

5. Before doing anything:
   - List valid files in ./ai-planning/
   - Sort by timestamp (newest first)
   - Read the most recent file

6. If exists â†’ continue
7. If not â†’ create new plan

==============================
PLAN â†’ EXECUTION GUARD
==============================

8. BEFORE execution:
   - ALL planning MUST be written into file

9. You MUST NOT execute if:
   - Plan is incomplete
   - Plan is not written

==============================
PLANNING STRUCTURE
==============================

10. Every plan MUST contain:

# <Title>

Timestamp: <YYYY-MM-DD HH:mm:ss>
Status: pending

## Objective

## Plan

Each step:

- [ ] Step: <action>
  - Files:
  - Change:
  - Outcome:

==============================
MODE: STRICT
==============================

11. In STRICT mode:

- Steps MUST be highly detailed
- MUST include Technical Details
- MUST include Draft Code for complex parts

## Technical Details
- Reasoning
- Edge cases
- Approach

## Draft Code
```<language>
<code if needed>
```

- NO improvisation allowed
- Execution MUST follow plan exactly

==============================
MODE: BALANCED
==============================

12. In BALANCED mode:

- Steps MUST be clear but concise
- DO NOT include full code unless necessary

ONLY include code if:
- Logic is complex
- Ambiguity is high

- Small implementation decisions allowed during execution

==============================
EXECUTION RULE
==============================

13. You MUST NOT execute anything outside the plan

14. If new logic is needed:
   - STOP
   - Update plan
   - Then continue

15. During execution:
   - Match every action to a step

==============================
PLAN COMPLETENESS CHECK
==============================

16. Before execution, verify:

- Are steps clear enough to execute?
- Are important details missing?

17. If incomplete:
   - Expand plan first

==============================
PLAN UPDATE
==============================

18. After each step:
   - Mark [x]
   - Update status

19. If plan changes:
   Append:

## Revision <timestamp>
- Changes
- Reason

==============================
SOURCE OF TRUTH
==============================

20. Only source:
   ./ai-planning/

21. Never rely on:
   - memory
   - chat history
   - latest.md

==============================
OUTPUT
==============================

22. Always report:
   - Mode used
   - File path
   - Action:
     CREATED PLAN / UPDATED PLAN / CONTINUING PLAN

==============================
FAIL-SAFE
==============================

23. If about to execute without proper plan:
   STOP