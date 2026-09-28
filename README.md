# VDS-FIXES

Backup + fix for the **Event Date filter bug** on the coordinator report page
`https://backend.vaidicpujas.org/coordinator_reports/?q_eventID=...`
(WordPress theme `vds`, live site `backend.vaidicpujas.org`).

This repository holds the **original (unmodified) copies** of the two files that
will be changed, so the change can be rolled back at any time.

---

## 1. The bug (symptom)

On the report page, selecting an Event Date showed **fewer rows** than the real
number of registrations for that day.

Example, Event ID `E73967`, Event Date `09/28/2026`:

| Where | Result for 09/28/2026 |
|---|---|
| Report page with date filter | **5 rows** (wrong) |
| "Download All Details" export, then filter "Event Date" in Excel | **26 rows** (correct) |

## 2. Root cause

Every registration stores its event date as **text** in the database column
`eventDate` of table `wp_vds_contrib`.

The **same calendar date was saved in different text formats**:

- `09/28/2026`  → 5 rows  (format `MM/DD/YYYY`)
- `2026-09-28`  → 21 rows (format `YYYY-MM-DD`)
- (rare, other days: `28 Sep 2026`, and a literal `Daily` marker)

Total for 2026-09-28 = **26**.

Both the on-screen table and the export filter the column with an **exact
equality** match:

```php
$whereAll .= " AND eventDate='" . $event_date . "'";
```

So a filter for `09/28/2026` only matches the 5 rows stored in that exact
format and ignores the 21 rows stored as `2026-09-28`.

Excel showed all 26 because Excel automatically converts **both** text styles
into the same real date when it filters — the website's SQL filter does not.

Verified live (read-only) against the report's own endpoint:

```
event_date = '09/28/2026'  -> 5 rows
event_date = '2026-09-28'  -> 21 rows
event_date = ''            -> 3398 rows  (all)
```

## 3. Files being changed

Both files live in the active theme, i.e. on the server at:

```
wp-content/themes/vds/assets/vkutable/server_processing_seva.php   (on-screen table)
wp-content/themes/vds/assets/vkutable/api_download_contrib_data.php (Download All Details export)
```

The originals are backed up in this repo under:

```
backups/wp-content/themes/vds/assets/vkutable/server_processing_seva.php
backups/wp-content/themes/vds/assets/vkutable/api_download_contrib_data.php
```

SHA-256 of the originals:

```
8a72610b03bfd9e62b5eeff39d5453f7b03d8a33b8f1c6941d597cf7264a5dfb  server_processing_seva.php
e73b38d8d6be4bd9fd069627d2a4f69a5710183b3798331f24ffbabcdcb8f2bf  api_download_contrib_data.php
```

## 4. The change

In **both** files, replace this block...

```php
if (!empty($event_date)) {
	$whereAll .= " AND eventDate='" . $event_date . "'";
}
```

...with this block:

```php
if (!empty($event_date)) {
	$ts = strtotime($event_date);
	if ($ts) {
		$ed1 = date('m/d/Y', $ts); // 09/28/2026
		$ed2 = date('Y-m-d', $ts); // 2026-09-28
		$ed3 = date('j M Y', $ts); // 28 Sep 2026
		$whereAll .= " AND (eventDate='" . $ed1 . "' OR eventDate='" . $ed2 . "' OR eventDate='" . $ed3 . "')";
	} else {
		$whereAll .= " AND eventDate='" . $event_date . "'";
	}
}
```

Exact locations:

| File | Line(s) of the block being replaced |
|---|---|
| `assets/vkutable/server_processing_seva.php` | 324–326 |
| `assets/vkutable/api_download_contrib_data.php` | 137–139 |

No other line is touched. No database change is made.

### Why this fixes it

The date picked in the form (e.g. `09/28/2026`) is converted into the three
formats that exist in the column, and the row matches if **any** of them matches.
So all 5 + 21 = 26 rows come back. The export behaves the same way, so a dated
download also returns all 26 rows.

## 5. How to roll back

If anything goes wrong, restore the original file(s) from the `backups/` folder
back to the same server path (via WordPress **Appearance → Theme File Editor**,
or by copying the file over SFTP). The backed-up files are byte-for-byte the
pre-change versions.

## 6. How to verify after the change

Open `https://backend.vaidicpujas.org/coordinator_reports/?q_eventID=E73967`,
set Event Date = `09/28/2026` and search. The table (and the "Download All
Details : All N Results" count) should now show **26**, matching Excel.

## 7. Notes / follow-up

- The underlying cause is that event dates are written to `wp_vds_contrib.eventDate`
  in **inconsistent formats** by different code paths. This fix makes the report
  tolerant of all known formats. A cleaner long-term fix is to standardise how
  `eventDate` is saved and/or normalise existing rows — a separate task.
- The literal value `Daily` (3 rows) is a recurring marker, not a real date, so
  it can never match a specific day. Expected.
- The same exact-match pattern (`eventDate='...'`) may exist in other report
  files (finance / coordinator / vvmvp). If a date filter there shows short
  counts, apply the same block.

---

## 8. Applied (to be confirmed)

The change above was applied to the live site on 2026-09-28 via
WordPress **Appearance → Theme File Editor**, on these two files only:

- `assets/vkutable/server_processing_seva.php`
- `assets/vkutable/api_download_contrib_data.php`

Post-change verification (Event ID `E73967`):

| Filter | Before | After |
|---|---|---|
| Report page, Event Date = `09/28/2026` | 5 | **26** |
| Table endpoint, `event_date=09/28/2026` | 5 | **26** |
| Export endpoint, `event_date=09/28/2026` | 5 | **26** |
| Table endpoint, `event_date=10/13/2026` (stored only as `2026-10-13`) | 0 | **1** |

The exact post-change copies are kept under `fixed/` for reference.

To roll back: restore the two files in `backups/` (pre-change versions) over the
live files.

---

## 9. Other fixes in this repo

| Fix | Folder | Status |
|---|---|---|
| Event Date calendar missing for recurring events in the Offline Cart (E79280) | [`recurring-event-calendar-fix/`](./recurring-event-calendar-fix/README.md) | applied & verified |
