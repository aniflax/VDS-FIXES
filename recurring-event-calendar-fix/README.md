# Fix: Event Date calendar missing for recurring events in the Offline Cart

Date: **2026-09-28**
Site: `backend.vaidicpujas.org` (WordPress theme `vds`)
Page: `https://backend.vaidicpujas.org/offlinecart/`

This folder is the **backup + record** for the recurring-event calendar fix. It
holds the **original (unmodified) copies** of the files that are changed, so the
change can be rolled back at any time. Nothing here is auto-deployed — it is a
manual record.

Two related changes:

- **Part A** — make the Event Date calendar appear for daily recurring events
  that use custom sankalpas (e.g. E79280). *(applied & verified)*
- **Part B** — limit the selectable dates to the event's date window (never
  before today / the start date, never after the end date). *(backed up; applying)*

---

## 1. The bug (symptom)

In the Offline Cart, you fill in the person's details, search for an event, and
click **Add**. A popup opens with the sankalpa options, and for a **recurring
(daily)** event there should be an **"Event Date"** calendar so you can pick the
date to register for.

- Event ID **E73967** (Ayushya Homa, recurring daily) → calendar **appears**. ✅
- Event ID **E79280** (Pitru Paksha Spl Gau Puja, recurring daily) → calendar
  **missing**. ❌

So E79280 is genuinely recurring but the date box never shows.

## 2. Root cause

The popup is rendered by:

```
wp-content/themes/vds/cartpayments/v-vds_manage_cart_offline_single_payments.php
```

Inside it, the JavaScript only reveals the date box when the data it fetches
contains `repeat_frequency == "daily"` (that check is on line **2041**). The data
comes from:

```
wp-content/themes/vds/api_get_customSankalpas.php
```

That file has two branches:

| Event type | Branch | Returned fields | Calendar |
|---|---|---|---|
| Normal event | `else` | includes `repeat_frequency`, `event_start_date` | shows ✅ |
| Event with **custom/additional sankalpas** (`additionalSankalpa == 1`) | `if` | only the sankalpa rows — **no** recurrence fields | never shows ❌ |

E73967 has `additionalSankalpa = 0` → goes to `else` → works.
E79280 has `additionalSankalpa = 1` → goes to `if` → the recurrence fields are
**dropped**, so the JavaScript can't show the calendar even though the event is
daily.

Verified live (read-only):

- E73967 → `additionalSankalpa = 0`, API returns `"repeat_frequency":"daily"`.
- E79280 → `additionalSankalpa = 1`, API returns only `Gau Puja / Gau Daan` rows,
  no `repeat_frequency`.
- E79280 matches `repeat_frequency = 'daily'` (and nothing else) in the events DB,
  confirming it really is a daily recurring event.

## 3. File being changed

```
wp-content/themes/vds/api_get_customSankalpas.php
```

Original backed up in this folder at:

```
recurring-event-calendar-fix/backups/wp-content/themes/vds/api_get_customSankalpas.php
```

SHA-256 of the original:

```
e658e6bb2bf53aed2d37567c73cb6a342e50a0ee2b349f7c5e97a8d51d5a9efa
```

Size: 1639 bytes.

## 4. The change

Inside the `if ($additionalSankalpa == 1){ ... }` block, immediately **after
line 20** (the `$wpdb->get_results(...)` line), insert:

```php
	// Recurring-event fix: pass the EVENT's recurrence to the popup so the date
	// calendar shows for daily-recurring events that use custom sankalpas (e.g. E79280).
	foreach ($customSankalpas as $k => $row) {
		$customSankalpas[$k]['event_start_date'] = $eventDetailsData[0]['event_start_date'];
		$customSankalpas[$k]['repeat_frequency']  = $eventDetailsData[0]['repeat_frequency'];
	}
```

No other line is touched. No database change is made. The JavaScript in
`cartpayments/v-vds_manage_cart_offline_single_payments.php` is **not** changed —
its existing check (`== "daily"`) already works once the data is present.

### Why this fixes it

The event's own `repeat_frequency` and `event_start_date` (already loaded into
`$eventDetailsData` on line 15) are copied onto each sankalpa row that is sent
back. The popup now sees `repeat_frequency = "daily"` for E79280 and reveals the
date calendar — exactly like it already does for E73967.

The exact post-change copy is kept under `fixed/`.

## 5. How to roll back

Restore the original file from `backups/` over the live file (via WordPress
**Appearance → Theme File Editor**, or SFTP). The backed-up file is the
pre-change version (SHA-256 above).

## 6. How to verify after the change

1. Open `https://backend.vaidicpujas.org/offlinecart/` and hard-refresh (Ctrl+F5).
2. Fill in details, search **E79280**, click **Add**.
3. The **Event Date** calendar should now be visible and usable.
4. Optionally re-check E73967 still shows its calendar.

The API can also be checked directly:

```
/wp-content/themes/vds/api_get_customSankalpas.php?eventid=E79280
```

Before the fix it returned sankalpa rows with no `repeat_frequency`; after the fix
each row also carries `"repeat_frequency":"daily"` and `"event_start_date":"..."`.

## 7. Status

Applied to the live site on **2026-09-28** via WordPress
**Appearance → Theme File Editor** (file `api_get_customSankalpas.php`, theme `vds`).

- [x] Original backed up
- [x] Fix applied to the live site
- [x] Post-change verified

Live file after the change:

```
sha256: 7c2e2cbfd6b1a90cfa37c4c8643f570d9e7457bae7caeba18a03eae3d5b5e708
bytes:  2027
```

(Identical to `fixed/wp-content/themes/vds/api_get_customSankalpas.php`.)

Post-change verification:

| Check | Before | After |
|---|---|---|
| API `?eventid=E79280` returns `repeat_frequency` | absent | `"daily"` |
| Offline Cart popup, E79280 → Event Date box visible | no | **yes** (value `2026-09-27`) |
| Offline Cart popup, E73967 → Event Date box visible (regression check) | yes | **yes** (still works) |

The API was also confirmed to return HTTP 200 with valid JSON after the change,
so there is no PHP syntax error.

## 8. Notes / follow-up

- Only **daily** recurrence is covered, because that is what the popup checks for
  and what E79280 is. If weekly/monthly/yearly recurring events should also show
  the calendar, the separate check on line 2041 of
  `cartpayments/v-vds_manage_cart_offline_single_payments.php` would also need to
  be relaxed (e.g. show for any non-empty `repeat_frequency` other than
  `nonRecurring`). That is a larger change and is **not** part of this fix.

---

# Part B — Limit the selectable dates to the event's date window

Date: **2026-09-28** (follow-up to Part A).

## B1. Requirement

For **daily recurring** events, the Event Date calendar must only allow dates
inside the event's own window:

- **no date before today** (and never before the event start date), and
- **no date after the event end date**.

Example — **E79280** runs **27 Sep 2026 → 10 Oct 2026**. On 28 Sep 2026:

- 27 Sep must **not** be selectable (in the past),
- 11 Oct and later must **not** be selectable (after the event end date),
- 28 Sep … 10 Oct are selectable.

Before Part B the picker only had `minDate: new Date()` and **no** `maxDate`, so
dates after the event end could still be picked (and the box pre-filled with the
raw start date, even when that was in the past).

## B2. Files changed

```
wp-content/themes/vds/api_get_customSankalpas.php                                  (also send event_end_date)
wp-content/themes/vds/cartpayments/v-vds_manage_cart_offline_single_payments.php   (limit the picker)
```

Originals backed up at:

```
recurring-event-calendar-fix/backups/wp-content/themes/vds/api_get_customSankalpas.php
recurring-event-calendar-fix/backups/wp-content/themes/vds/cartpayments/v-vds_manage_cart_offline_single_payments.php
```

Original SHA-256:

```
e658e6bb2bf53aed2d37567c73cb6a342e50a0ee2b349f7c5e97a8d51d5a9efa  api_get_customSankalpas.php
b4476c5aa0f1bf3e5c6923419e08148ac5f0bd2693bfe26890b2b0b3dd3868a6  v-vds_manage_cart_offline_single_payments.php
```

## B3. The change

**(a) `api_get_customSankalpas.php`** — also return `event_end_date` (it already
returned `event_start_date` / `repeat_frequency`). One line added in each branch:

```php
$customSankalpas[$k]['event_end_date'] = $eventDetailsData[0]['event_end_date'];   // custom-sankalpa branch
$mySankalpaObj->event_end_date = $eventDetailsData[0]['event_end_date'];           // standard branch
```

**(b) `cartpayments/v-vds_manage_cart_offline_single_payments.php`**:

1. New helper function, added just above the `#DescModal` `show.bs.modal` handler:

```js
function vdsParseEventDate(vdsStr) {
    if (!vdsStr) { return null; }
    var vdsParts = String(vdsStr).replace(/\//g, "-").split("-");
    if (vdsParts.length === 3 && vdsParts[0].length === 4) {
        return new Date(parseInt(vdsParts[0], 10), parseInt(vdsParts[1], 10) - 1, parseInt(vdsParts[2], 10));
    }
    var vdsD = new Date(vdsStr);
    if (isNaN(vdsD.getTime())) { return null; }
    vdsD.setHours(0, 0, 0, 0);
    return vdsD;
}
```

2. Inside the `if (value.repeat_frequency == "daily")` block, the old line
   `$("#custom_event_date").val(value.event_start_date);` is replaced by the
   window logic (the three `prop(...)`/`datepicker(... "disabled" ...)` lines
   stay as they were):

```js
var vdsToday = new Date(); vdsToday.setHours(0, 0, 0, 0);
var vdsStart = vdsParseEventDate(value.event_start_date);
var vdsEnd = vdsParseEventDate(value.event_end_date);
var vdsMin = vdsToday;
if (vdsStart && vdsStart.getTime() > vdsToday.getTime()) { vdsMin = vdsStart; }
var vdsDefault = vdsMin;
if (vdsEnd && vdsDefault.getTime() > vdsEnd.getTime()) { vdsDefault = vdsEnd; }
$("#custom_event_date").datepicker("option", "minDate", vdsMin);
$("#custom_event_date").datepicker("option", "maxDate", vdsEnd ? vdsEnd : null);
$("#custom_event_date_lbl").prop("hidden", false);
$("#custom_event_date").prop("hidden", false);
$("#custom_event_date").datepicker("option", "disabled", false);
$("#custom_event_date").val($.datepicker.formatDate("mm/dd/yy", vdsDefault));
```

Fixed copies (post-change) are kept at `fixed/...`:

```
a0c4d6fd0aace2eadfc574d82d69bc303909b085e7f714dfa257a72a567c0dec  fixed/.../api_get_customSankalpas.php
c0ba36938171f3f082523ff59e0d7bcaa68c5dbb4f6aee7ec1af7c16aa04877b  fixed/.../cartpayments/v-vds_manage_cart_offline_single_payments.php
```

## B4. Why this works

`vdsMin` = the later of **today** and the **event start date**, and `vdsMax` =
the **event end date**. jQuery UI's `minDate`/`maxDate` then grey out everything
outside that range, so only valid dates can be picked. The field is pre-filled
with `vdsMin` (clamped to the end date if needed), so it never shows an
unselectable date.

## B5. Result

For E79280 on 28 Sep 2026: the picker opens with **28 Sep 2026** selected and
only **28 Sep … 10 Oct 2026** selectable. The same rule applies to **every**
daily recurring event.

## B6. How to roll back (Part B)

Restore the two files in `backups/` over the live files (via WordPress
**Appearance → Theme File Editor**, or SFTP).

## B7. Status (Part B)

- [x] Originals backed up
- [ ] Applied to the live site
- [ ] Post-change verified
