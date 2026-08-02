# Graff Kids Store - OSPOS Customizations

This document serves as a comprehensive changelog and guide for all custom modifications made to this Open Source Point of Sale (OSPOS) installation for **Graff Kids Store**.

If you are a new developer taking over this project, please review this document to understand what core files were modified and why.

---

## Deployment Guide (Migrating to a New Installation)
If you are moving these customizations to a completely brand new OSPOS installation, simply copy and replace the following files from this repository to your fresh OSPOS directory:

**Views (`app/Views/`)**
- `partial/header.php`
- `partial/footer.php`
- `home/home.php`
- `login.php`
- `sales/receipt_default.php`
- `sales/receipt_short.php`
- `sales/register.php`
- `items/form.php`
- `configs/message_config.php`
- `messages/sms.php`
- `messages/form_sms.php`
- `people/manage.php`

**Controllers (`app/Controllers/`)**
- `Config.php`
- `Sales.php`
- `Messages.php`
- `Customers.php`

**Libraries (`app/Libraries/`)**
- `WhatsappLib.php` *(Brand new file)*
- `Sale_lib.php`

*Note: Also ensure that your `public/uploads/` directory has write permissions enabled for saving the dynamically generated PDF receipts and media attachments!*

---

## Phase 1: Branding & UI Overhaul
**Goal**: Match the OSPOS interface to the Graff Kids Store brand identity (Custom colors, fonts, and logos).

### Files Modified:
1. `app/Views/partial/header.php`
   - Injected custom CSS into the `<head>`.
   - Imported Google Fonts (`Montserrat` and `League Spartan`).
   - Changed global background to Cream (`#FFFCF3`) and text to Dark Gray (`#5D646A`).
   - Updated the Topbar and Navbar colors to Mint/Blue (`#88BEE2`) and buttons to Yellow/Orange (`#EEA72E`).
   - Replaced the default logo image with the text "GRAFF" styled using Montserrat (Ultra-Bold 900).
   - Added `padding-bottom: 60px !important;` to the `body` tag to accommodate the fixed footer and prevent it from covering content (like Submit buttons).

2. `app/Views/partial/footer.php`
   - Replaced the default OSPOS version footer with a custom sticky footer.
   - Added a `60px` spacer div to ensure the footer doesn't overlap scrollable content.
   - Styled the footer to stick to the bottom (`position: fixed; bottom: 0;`), colored it Mint/Blue, and added the link `https://graffkids.com`.

3. `app/Views/home/home.php`
   - Replaced the hardcoded "Welcome to OSPOS!" message with a dynamic greeting: `Welcome to <?= esc($config['company']) ?>`.

4. `app/Views/login.php`
   - Modified the login screen to use the company name from Store Config instead of hardcoded OSPOS branding in the `<title>`, welcome message, and footer.

5. `app/Views/sales/receipt_default.php` & `app/Views/sales/receipt_short.php`
   - Fixed receipt sizing issues that caused massive fonts and scaling problems.
   - Forced a strict `max-width: 320px` (standard thermal receipt printer size).
   - Forced `font-size: 12px !important;` to override conflicting styles from the custom header.

---

## Phase 2: Business Logic Updates (Cost Price Automation)
**Goal**: Automatically calculate the Cost Price as 55% of the Unit Price when creating or editing items.

### Files Modified:
1. `app/Views/items/form.php`
   - Hidden the `<div id="attributes">` block entirely using HTML comments.
   - Injected a jQuery script at the bottom of the file that listens to the `unit_price` input field on `keyup/change`. 
   - The script automatically calculates `55%` of the entered Unit Price and auto-fills it into the `cost_price` input field, rounded to two decimal places.

---

## Phase 3: WhatsApp & Twilio Integration
**Goal**: Integrate Twilio's API to automatically send WhatsApp receipts to customers, and allow bulk WhatsApp messaging.

### Files Modified:
1. `app/Views/configs/message_config.php`
   - Added three new configuration fields to the Store Config (Message tab):
     - Twilio Account SID
     - Twilio Auth Token
     - Twilio WhatsApp Number

2. `app/Controllers/Config.php`
   - Updated the `saveMessage()` method to whitelist and save the three new Twilio configuration fields into the `ospos_app_config` database table.

3. `app/Libraries/WhatsappLib.php` **(NEW FILE)**
   - Created a custom library that uses PHP cURL to securely connect to Twilio's WhatsApp API.
   - Includes logic to automatically sanitize phone numbers and append the `+91` Indian country code if missing.
   - Includes support for sending a `MediaUrl` to attach files.

4. `app/Controllers/Sales.php`
   - Modified the `complete()` method to intercept successful sales.
   - It checks if the customer has a phone number. If so, it uses `WhatsappLib` to instantly send a thank-you message containing the Sale ID and Total Amount via WhatsApp.

---

## Phase 4: Media Attachments & Email Marketing
**Goal**: Upgrade the Messages module to support marketing blasts via Email (using Gmail App Passwords) and WhatsApp, with support for File Attachments (Images, PDFs).

### Files Modified:
1. `app/Views/messages/sms.php` (Bulk Messaging)
   - Added "Email" to the Message Type dropdown.
   - Added a conditional "Subject" input field that appears when Email is selected.
   - Added a File Attachment input field (`accept="image/*,application/pdf"`).

2. `app/Views/messages/form_sms.php` (Single Customer Messaging)
   - Added the same Email option, conditional Subject field, and Attachment input as the bulk page.
   - Modified the Javascript validation to remove the strict "number" requirement from the recipient field so it can accept email addresses.
   - Added logic to dynamically swap the input value between the customer's Phone Number and Email Address depending on the chosen Message Type.

3. `app/Controllers/Messages.php`
   - Modified `send()` and `send_form()` to securely handle file uploads. Files are randomly renamed and saved to `public/uploads/whatsapp_media/`.
   - Updated the routing logic: 
     - If WhatsApp, it passes the public `media_url` to `WhatsappLib`.
     - If Email, it intercepts the comma-separated recipient list, loops through them, and uses OSPOS's native `Email_lib` to send the message and physical file attachment.

---

## Phase 5: Bulk Customer Selection Workflow
**Goal**: Allow the user to select multiple customers from the Customer Grid and seamlessly send them a marketing message.

### Files Modified:
1. `app/Views/people/manage.php`
   - Injected a new "Send Message" button into the main toolbar next to the native "Email" button.
   - Added jQuery logic to capture all checked `selected_ids`, create a hidden `<form>`, and POST the IDs to the server.

2. `app/Controllers/Customers.php`
   - Added a new `bulk_message()` method.
   - This method intercepts the POSTed Customer IDs (or 'all'), loops through the database to fetch their respective Phone Numbers and Email Addresses, filters out duplicates/empties, and redirects to `messages/sms` with the Recipients input magically pre-filled!

---
## Phase 6: Meta WhatsApp API Integration (Template Engine)
**Goal**: Integrate Meta's Developer API as an alternative to Twilio, utilizing strict Message Templates to allow sending receipts and marketing blasts to new customers without 24-hour window restrictions.

### Files Modified:
1. `app/Views/configs/message_config.php` & `app/Controllers/Config.php`
   - Added an API Provider dropdown (Twilio vs Meta).
   - Added fields for Meta Access Token, Meta Phone Number ID, Meta Receipt Template, and Meta Marketing Template.
   - Added an Automation Toggle (`whatsapp_receipt_mode`) to choose between Automatic or Manual sending of receipts.

2. `app/Views/sales/register.php` & `app/Controllers/Sales.php`
   - Added a "WhatsApp Receipt" checkbox to the POS register (conditionally checked based on the Automation toggle).
   - Upgraded the `complete()` logic to capture the checkbox state, dynamically build the PDF receipt, and route it through the selected API provider.

3. `app/Libraries/WhatsappLib.php`
   - Completely rewrote the library to support both Twilio and Meta.
   - Built a robust Meta Template engine (`sendTemplate()`) capable of injecting dynamically generated PDFs into `document` headers, and user-uploaded marketing files into `image` or `document` headers.

4. `app/Controllers/Messages.php`
   - Updated the bulk messaging engine to use Meta Marketing Templates if configured, passing the uploaded file type and URL to the header dynamically.

### Action Required: Meta Template Setup Guide
For the Meta API to work flawlessly outside of the 24-hour window, you MUST create these exact templates in your Meta Business Manager:

1. **Receipt Template**
   - **Name**: (Enter whatever you named it in the OSPOS config, e.g., `graff_receipt`)
   - **Category**: Utility
   - **Header Type**: Media -> Document
   - **Body Text**: "Thank you for shopping at Graff Kids Store! Please find your receipt attached above. Visit us again!" (Keep it static, no `{{1}}` variables needed).

2. **Marketing Template**
   - **Name**: (Enter whatever you named it in the OSPOS config, e.g., `graff_marketing`)
   - **Category**: Marketing
   - **Header Type**: Media -> Image (or Document, depending on the type of files you plan to upload the most).
   - **Body Text**: `{{1}}` (This is mandatory! OSPOS will dynamically replace this variable with the message you type into the Messages box).

---
*End of Customizations Changelog*
