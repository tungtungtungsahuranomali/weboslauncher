# Plan: fix-back-button-navigation-on-webos-tv-l

> Created: 2026-06-26 13:48:24
> **Status**: Completed

## Objective

Fix Back button navigation on webOS TV launcher sub-pages

## Scope

**In Scope:**
- Fix 5 sub-page HTML files that don't handle webOS remote Back key (`key === 'Back'`, `keyCode === 4`)
- webOS Launcher index.html (already handles Back but does nothing — correct for main page)

**Out of Scope:**
- Android TV / non-webOS platforms
- Dining cart/focus logic changes (only add key detection)

## Context

webOS TV app at C:/laragon/www/takeoffwebos/webos/index.html. Sub-pages are served from takeoffserver PHP backend (C:/laragon/www/takeoffserver/*.html). 

**Root Cause:** webOS remote Back button sends `e.key === 'Back'` / `e.keyCode === 4`. 
Most sub-pages only check for `Backspace` (keyCode 8) and `Escape` (keyCode 27).

**Already works (handle webOS Back correctly):**
- information.html, promotion.html, general_info.html, facilities.html
- These all check: `key === 'Backspace' || key === 'Escape' || key === 'Back' || keyCode === 4 || keyCode === 8 || keyCode === 27`

**Broken (missing webOS Back):**
| File | Line | Current Check | Missing |
|------|------|---------------|--------|
| transport.html | 313 | `Backspace`/`Escape` | `Back`/keyCode 4 |
| dining.html | 1146 | `Backspace`/`Escape` | `Back`/keyCode 4 |
| iptv.html | 427 | `Backspace`/`Escape` (switch) | `Back` case |
| info_player.html | 132 | `Backspace`/`Escape` | `Back`/keyCode 4 |
| amenities.html | 564 | `Backspace`/`Escape` | `Back`/keyCode 4 |

## Acceptance Criteria

Pressing Back button on webOS remote from any sub-page returns to main launcher index.php.

## Approach

Add `|| key === 'Back' || keyCode === 4` to each broken sub-page's Back condition.
For iptv.html (switch statement), add `case 'Back':` before `case 'Backspace':`.

## Tasks

| # | Task | Files | Status |
|---|------|-------|--------|
| 1 | Fix transport.html — add webOS Back key detection | C:/laragon/www/takeoffserver/transport.html | pending |
| 2 | Fix dining.html — add webOS Back key detection | C:/laragon/www/takeoffserver/dining.html | pending |
| 3 | Fix iptv.html — add webOS Back case in switch | C:/laragon/www/takeoffserver/iptv.html | pending |
| 4 | Fix info_player.html — add webOS Back key detection | C:/laragon/www/takeoffserver/info_player.html | pending |
| 5 | Fix amenities.html — add webOS Back key detection | C:/laragon/www/takeoffserver/amenities.html | pending |

## Risks & Mitigations

- iptv.html uses switch statement — need to add case, not modify existing comparison
- dining.html has context-aware back logic (cart/focus states) — only add key detection, don't change behavior

## Verification

- [x] Root cause identified (webOS remote sends `Back` not `Backspace`)
- [x] All 5 broken files identified
- [x] Fix applied and verified
