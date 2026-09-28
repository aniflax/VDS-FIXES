# Fix: Event Date calendar missing for recurring events in the Offline Cart

Date: **2026-09-28**
Site: `backend.vaidicpujas.org` (WordPress theme `vds`)
Page: `https://backend.vaidicpujas.org/offlinecart/`

This folder is the **backup + record** for one small fix. It holds the
**original (unmodified) copy** of the file that is changed, so the change can be
rolled back at any time. Nothing here is auto-deployed — it is a manual record.

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

- [x] Original backed up (this commit)
- [ ] Fix applied to the live site
- [ ] Post-change verified

## 8. Notes / follow-up

- Only **daily** recurrence is covered, because that is what the popup checks for
  and what E79280 is. If weekly/monthly/yearly recurring events should also show
  the calendar, the separate check on line 2041 of
  `cartpayments/v-vds_manage_cart_offline_single_payments.php` would also need to
  be relaxed (e.g. show for any non-empty `repeat_frequency` other than
  `nonRecurring`). That is a larger change and is **not** part of this fix.
