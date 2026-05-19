-- ═══════════════════════════════════════════════
--  MedVault — School Clinic Inventory System
--  Database Setup Script  (v1.1)
--
--  FILE STRUCTURE:
--    index.php               → Root redirect
--    login.php               → Login page
--    logout.php              → Session destroy
--    dashboard.php           → Stats + recent entries
--    medicines.php           → Full list (search/filter)
--    add_medicine.php        → Add new medicine
--    edit_medicine.php       → Edit existing medicine
--    view_medicine.php       → View detail
--    delete_medicine.php     → Delete handler
--    expiring.php            → Expiring within 60 days
--    lowstock.php            → Low/out of stock list
--    profile.php             → Change password
--    print_inventory.php     → Print/PDF report
--    config/db.php           → DB connection
--    config/session.php      → Auth helpers
--    config/helpers.php      → Utility functions
--    includes/header.php     → Topbar + sidebar
--    includes/footer.php     → JS footer
--    assets/css/style.css    → Stylesheet
--    assets/js/main.js       → JS logic
--
--  HOW TO USE:
--    1. Place /clinic folder in XAMPP htdocs/
--    2. Run this SQL in phpMyAdmin
--    3. Visit http://localhost/clinic/
--    4. Login: admin / admin123
-- ═══════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS clinic_inventory_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE clinic_inventory_db;

-- ── ADMIN TABLE ──
CREATE TABLE IF NOT EXISTS admin (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50)  NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- ── MEDICINES TABLE ──
CREATE TABLE IF NOT EXISTS medicines (
  medicine_id     INT AUTO_INCREMENT PRIMARY KEY,
  medicine_name   VARCHAR(100) NOT NULL,
  category        VARCHAR(50),
  quantity        INT          DEFAULT 0,
  unit            VARCHAR(20),
  expiration_date DATE,
  date_added      DATE,
  status          VARCHAR(20)  DEFAULT 'In Stock',
  remarks         TEXT
);

-- ── DEFAULT ADMIN ACCOUNT ──
-- Username: admin | Password: admin123 (plain text for demo)
-- For production: use password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admin (username, password) VALUES ('admin', 'admin123')
  ON DUPLICATE KEY UPDATE username = username;

-- ── SAMPLE MEDICINE DATA ──
INSERT INTO medicines (medicine_name, category, quantity, unit, expiration_date, date_added, status, remarks) VALUES
  ('Paracetamol 500mg',   'Analgesic',          150, 'tablets',  '2026-09-01', '2025-01-10', 'In Stock',     'Common pain reliever and fever reducer'),
  ('Amoxicillin 250mg',   'Antibiotic',           8, 'capsules', '2025-12-15', '2025-02-05', 'Low Stock',    'Requires prescription; consult physician'),
  ('Cetirizine 10mg',     'Antihistamine',        60, 'tablets',  '2026-11-30', '2025-03-01', 'In Stock',     'For allergic reactions'),
  ('Antacid Chewable',    'Antacid',               0, 'tablets',  '2026-06-20', '2025-01-20', 'Out of Stock', 'Reorder needed urgently'),
  ('Betadine Solution',   'Antiseptic',            5, 'bottles',  '2025-05-01', '2024-10-10', 'Expired',      'Dispose properly per school policy'),
  ('Vitamin C 500mg',     'Vitamin/Supplement',  200, 'tablets',  '2027-01-15', '2025-04-01', 'In Stock',     'Daily supplement for students'),
  ('Ibuprofen 200mg',     'Analgesic',            45, 'tablets',  '2026-08-10', '2025-02-20', 'In Stock',     'Anti-inflammatory; take with food'),
  ('Loperamide 2mg',      'Other',                30, 'capsules', '2026-05-25', '2025-01-15', 'In Stock',     'For diarrhea relief'),
  ('Oral Rehydration Salt','Vitamin/Supplement',  10, 'sachets',  '2026-03-30', '2025-03-10', 'Low Stock',    'Dissolve in 1L water'),
  ('Elastic Bandage',     'First Aid',            20, 'pieces',   NULL,          '2025-04-05', 'In Stock',     'Various sizes available');


ALTER TABLE medicines ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER remarks;