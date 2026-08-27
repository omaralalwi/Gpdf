# Changelog

All notable changes to `gpdf` will be documented in this file

## 1.0.9 - 2026-08-28

Arabic shaping bugfixes:

- The Arabic percent sign `٪` (U+066A), and its neighbours up to U+066D, no longer emit a broken glyph entity that hid the text around them ([#13](https://github.com/omaralalwi/Gpdf/issues/13)).
- Numbers keep their reading order. A decimal or thousands separator used to split a number into two runs that came out reversed, and Arabic-Indic digits were reversed along with the surrounding letters — `١٠.٥٧` rendered as `٧٥.٠١` ([#12](https://github.com/omaralalwi/Gpdf/issues/12)).
- `showNumbersAsHindi` now returns Arabic-Indic digits in reading order, including for numbers that stand on their own.
- Arabic typed on a Persian or Urdu keyboard renders correctly. Letters such as `ھ` (U+06BE) and `ی` (U+06CC) used to split a sentence into fragments that were each reversed on their own ([#11](https://github.com/omaralalwi/Gpdf/issues/11)).

No public API or configuration changes.

## 1.0.0 - 201X-XX-XX

- initial release
