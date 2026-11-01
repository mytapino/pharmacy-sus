-- =========================================================
-- Pharmacy System — PostgreSQL schema + ข้อมูลตัวอย่าง (สำหรับ Supabase)
-- แปลงมาจาก pharmacy_system_mock_names.sql (ข้อมูลสมมติทั้งหมด)
-- วิธีใช้: Supabase Dashboard > SQL Editor > New query > วางทั้งไฟล์ > Run
-- รันซ้ำได้: ตารางเดิมจะถูกลบและสร้างใหม่ (ข้อมูลที่ผู้ทดสอบเพิ่มจะหายด้วย)
-- =========================================================

BEGIN;

SET LOCAL TIME ZONE 'UTC';  -- เวลาใน dump เดิมเป็น UTC

DROP TABLE IF EXISTS order_items, orders, expired_logs, medicines, suppliers, employees, customers CASCADE;

CREATE TABLE customers (
  customer_id  SERIAL PRIMARY KEY,
  name         VARCHAR(100) NOT NULL,
  phone        VARCHAR(20),
  email        VARCHAR(100),
  address      TEXT,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE employees (
  employee_id  SERIAL PRIMARY KEY,
  name         VARCHAR(100) NOT NULL,
  position     VARCHAR(100),
  phone        VARCHAR(20),
  email        VARCHAR(100),
  hire_date    DATE,
  created_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
  status       VARCHAR(20) DEFAULT 'active'
);

CREATE TABLE expired_logs (
  log_id            SERIAL PRIMARY KEY,
  medicine_id       INTEGER,
  medicine_name     VARCHAR(255),
  quantity_cleared  INTEGER,
  cleared_by        INTEGER,
  cleared_at        TIMESTAMPTZ NOT NULL DEFAULT now(),
  expiry_date       DATE
);

CREATE TABLE suppliers (
  supplier_id     SERIAL PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  contact_person  VARCHAR(100),
  phone           VARCHAR(20),
  email           VARCHAR(100),
  address         TEXT,
  created_at      TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE TABLE medicines (
  medicine_id        SERIAL PRIMARY KEY,
  name               VARCHAR(150) NOT NULL,
  generic_name       VARCHAR(150),
  category           VARCHAR(100),
  supplier_id        INTEGER REFERENCES suppliers(supplier_id),
  unit_price         NUMERIC(10,2) NOT NULL,
  quantity_in_stock  INTEGER DEFAULT 0,
  expiry_date        DATE,
  created_at         TIMESTAMPTZ NOT NULL DEFAULT now(),
  unit_type          VARCHAR(50) DEFAULT 'เม็ด',
  image_url          VARCHAR(500),
  status             VARCHAR(10) DEFAULT 'active' CHECK (status IN ('active','inactive'))
);
CREATE INDEX ON medicines (supplier_id);

CREATE TABLE orders (
  order_id         SERIAL PRIMARY KEY,
  customer_id      INTEGER REFERENCES customers(customer_id),
  employee_id      INTEGER REFERENCES employees(employee_id),
  order_date       TIMESTAMPTZ NOT NULL DEFAULT now(),
  total_amount     NUMERIC(10,2),
  requireddate     DATE,
  shippeddate      DATE,
  status           VARCHAR(50),
  payment_method   VARCHAR(10) DEFAULT 'pending' CHECK (payment_method IN ('pending','cash','transfer')),
  amount_received  NUMERIC(10,2) DEFAULT 0.00,
  amount_change    NUMERIC(10,2) DEFAULT 0.00,
  payment_status   VARCHAR(10) DEFAULT 'unpaid' CHECK (payment_status IN ('unpaid','paid'))
);
CREATE INDEX ON orders (customer_id);
CREATE INDEX ON orders (employee_id);

CREATE TABLE order_items (
  order_item_id  SERIAL PRIMARY KEY,
  order_id       INTEGER REFERENCES orders(order_id),
  medicine_id    INTEGER REFERENCES medicines(medicine_id),
  quantity       INTEGER,
  unit_price     NUMERIC(10,2) NOT NULL DEFAULT 0.00
);
CREATE INDEX ON order_items (order_id);
CREATE INDEX ON order_items (medicine_id);

-- customers
INSERT INTO customers (customer_id, name, phone, email, address, created_at) VALUES
(1, 'ณภัทร วัฒนไพศาล', NULL, 'customer01@example.com', 'ที่อยู่สมมติ 01', '2026-09-01 08:00:00'),
(2, 'พิมพ์ลภัส รุ่งธารา', NULL, 'customer02@example.com', 'ที่อยู่สมมติ 02', '2026-09-01 08:00:00'),
(3, 'ธนกฤต ศิริพฤกษ์', NULL, 'customer03@example.com', 'ที่อยู่สมมติ 03', '2026-09-01 08:00:00'),
(4, 'ชญานิศ สุขวรางค์', NULL, 'customer04@example.com', 'ที่อยู่สมมติ 04', '2026-09-01 08:00:00'),
(5, 'กิตติภพ แสงนภา', NULL, 'customer05@example.com', 'ที่อยู่สมมติ 05', '2026-09-01 08:00:00'),
(6, 'อริสรา พูนวรินทร์', NULL, 'customer06@example.com', 'ที่อยู่สมมติ 06', '2026-09-01 08:00:00'),
(7, 'ปุณณวิช กุลธาริน', NULL, 'customer07@example.com', 'ที่อยู่สมมติ 07', '2026-09-01 08:00:00'),
(8, 'มนัสนันท์ จันทร์ภิรมย์', NULL, 'customer08@example.com', 'ที่อยู่สมมติ 08', '2026-09-01 08:00:00'),
(9, 'ภาคิน ธาราสกุล', NULL, 'customer09@example.com', 'ที่อยู่สมมติ 09', '2026-09-01 08:00:00'),
(10, 'กานต์พิชชา วิริยกุล', NULL, 'customer10@example.com', 'ที่อยู่สมมติ 10', '2026-09-01 08:00:00'),
(11, 'ธีรดนย์ พงศ์นภัทร', NULL, 'customer11@example.com', 'ที่อยู่สมมติ 11', '2026-09-01 08:00:00'),
(12, 'รินรดา เกษมธารา', NULL, 'customer12@example.com', 'ที่อยู่สมมติ 12', '2026-09-01 08:00:00'),
(13, 'สิรภพ อรุณวัฒน์', NULL, 'customer13@example.com', 'ที่อยู่สมมติ 13', '2026-09-01 08:00:00'),
(14, 'ณิชมน พิพัฒน์ธารา', NULL, 'customer14@example.com', 'ที่อยู่สมมติ 14', '2026-09-01 08:00:00'),
(15, 'วรเมธ รัตนพฤกษ์', NULL, 'customer15@example.com', 'ที่อยู่สมมติ 15', '2026-09-01 08:00:00'),
(16, 'พัชราภา สุขธาริน', NULL, 'customer16@example.com', 'ที่อยู่สมมติ 16', '2026-09-01 08:00:00'),
(17, 'จิรายุ วงศ์ภัทรา', NULL, 'customer17@example.com', 'ที่อยู่สมมติ 17', '2026-09-01 08:00:00'),
(18, 'ธัญชนก นภาสิริ', NULL, 'customer18@example.com', 'ที่อยู่สมมติ 18', '2026-09-01 08:00:00'),
(19, 'อัครพล พูนพิพัฒน์', NULL, 'customer19@example.com', 'ที่อยู่สมมติ 19', '2026-09-01 08:00:00'),
(20, 'ปภาวรินทร์ ศรีธารา', NULL, 'customer20@example.com', 'ที่อยู่สมมติ 20', '2026-09-01 08:00:00'),
(9999, 'ลูกค้าทั่วไป (Walk-in)', NULL, NULL, NULL, '2026-09-01 08:00:00');

-- employees
INSERT INTO employees (employee_id, name, position, phone, email, hire_date, created_at, status) VALUES
(1, 'นราวิชญ์ ศิริธารา', 'เภสัชกร', NULL, 'employee01@example.com', '2026-01-01', '2026-09-01 08:00:00', 'active'),
(2, 'พิชชาภา วัฒนกุลชัย', 'ผู้ช่วยเภสัชกร', NULL, 'employee02@example.com', '2026-01-01', '2026-09-01 08:00:00', 'active'),
(3, 'ชยพล รุ่งนภากร', 'แคชเชียร์', NULL, 'employee03@example.com', '2026-01-01', '2026-09-01 08:00:00', 'active'),
(4, 'กมลชนก ธาราพิพัฒน์', 'พนักงานสต็อก', NULL, 'employee04@example.com', '2026-01-01', '2026-09-01 08:00:00', 'active'),
(5, 'วีรภัทร สุขวรินทร์', 'พนักงานขาย', NULL, 'employee05@example.com', '2026-01-01', '2026-09-01 08:00:00', 'active');

-- expired_logs
INSERT INTO expired_logs (log_id, medicine_id, medicine_name, quantity_cleared, cleared_by, cleared_at, expiry_date) VALUES
(1, 1, 'Paracetamol 500mg', 2, 1, '2026-09-01 09:00:00', '2026-08-31'),
(2, 5, 'Cetirizine 10mg', 4, 1, '2026-09-01 09:00:00', '2026-08-31'),
(3, 9, 'ORS Sachet', 6, 1, '2026-09-01 09:00:00', '2026-08-31');

-- suppliers
INSERT INTO suppliers (supplier_id, name, contact_person, phone, email, address, created_at) VALUES
(1, 'นภาเมด ซัพพลาย', 'กฤตภาส แสงวรินทร์', NULL, 'supplier01@example.com', 'ที่อยู่บริษัทสมมติ 01', '2026-09-01 08:00:00'),
(2, 'ธาราฟาร์มา เทรด', 'พรลภัส กุลนภา', NULL, 'supplier02@example.com', 'ที่อยู่บริษัทสมมติ 02', '2026-09-01 08:00:00'),
(3, 'สิริเวช ดิสทริบิวชั่น', 'ธนินทร์ รัตนธารา', NULL, 'supplier03@example.com', 'ที่อยู่บริษัทสมมติ 03', '2026-09-01 08:00:00'),
(4, 'รุ่งวรินทร์ เมดิคอล', 'ศศิกานต์ พูนสิริ', NULL, 'supplier04@example.com', 'ที่อยู่บริษัทสมมติ 04', '2026-09-01 08:00:00'),
(5, 'พูนสุข เฮลท์ซัพพลาย', 'ปวริศ วัฒนธารา', NULL, 'supplier05@example.com', 'ที่อยู่บริษัทสมมติ 05', '2026-09-01 08:00:00'),
(6, 'อรุณธารา ฟาร์มา', 'ชลธิชา อรุณภิรมย์', NULL, 'supplier06@example.com', 'ที่อยู่บริษัทสมมติ 06', '2026-09-01 08:00:00'),
(7, 'กุลนภา เมดเทรด', 'นวพล สุขพิพัฒน์', NULL, 'supplier07@example.com', 'ที่อยู่บริษัทสมมติ 07', '2026-09-01 08:00:00'),
(8, 'วรินทร์เวช ซัพพลาย', 'กัญญาวีร์ จันทร์นภา', NULL, 'supplier08@example.com', 'ที่อยู่บริษัทสมมติ 08', '2026-09-01 08:00:00'),
(9, 'จันทร์สิริ เมดิคอล', 'ภูวดล ศิริวรางค์', NULL, 'supplier09@example.com', 'ที่อยู่บริษัทสมมติ 09', '2026-09-01 08:00:00'),
(10, 'ธาริน เฮลท์แคร์', 'รวิสรา พงศ์ธาริน', NULL, 'supplier10@example.com', 'ที่อยู่บริษัทสมมติ 10', '2026-09-01 08:00:00'),
(11, 'นภัทรฟาร์มา ซัพพลาย', 'ธีรภาส เกษมนภา', NULL, 'supplier11@example.com', 'ที่อยู่บริษัทสมมติ 11', '2026-09-01 08:00:00'),
(12, 'ศิริพฤกษ์ เมดเทรด', 'ณัฐริกา รุ่งภิรมย์', NULL, 'supplier12@example.com', 'ที่อยู่บริษัทสมมติ 12', '2026-09-01 08:00:00'),
(13, 'รัตนธารา เวชภัณฑ์', 'ปิยกร พูนธารา', NULL, 'supplier13@example.com', 'ที่อยู่บริษัทสมมติ 13', '2026-09-01 08:00:00'),
(14, 'สุขวรางค์ เมดิคอล', 'วรัญญา แสงพิพัฒน์', NULL, 'supplier14@example.com', 'ที่อยู่บริษัทสมมติ 14', '2026-09-01 08:00:00'),
(15, 'พิพัฒน์นภา ฟาร์มา', 'เมธาวิน กุลวรินทร์', NULL, 'supplier15@example.com', 'ที่อยู่บริษัทสมมติ 15', '2026-09-01 08:00:00');

-- medicines
INSERT INTO medicines (medicine_id, name, generic_name, category, supplier_id, unit_price, quantity_in_stock, expiry_date, created_at, unit_type, image_url, status) VALUES
(1, 'Paracetamol 500mg', 'Paracetamol', 'Pain Relief', 1, 4.00, 83, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(2, 'Amoxicillin 250mg', 'Amoxicillin', 'Antibiotic', 2, 6.50, 86, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(3, 'Ibuprofen 400mg', 'Ibuprofen', 'Pain Relief', 3, 7.00, 89, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(4, 'Vitamin C 500mg', 'Ascorbic acid', 'Vitamin', 4, 5.00, 92, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(5, 'Cetirizine 10mg', 'Cetirizine', 'Allergy', 5, 4.50, 95, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(6, 'Loratadine 10mg', 'Loratadine', 'Allergy', 6, 5.00, 98, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(7, 'Omeprazole 20mg', 'Omeprazole', 'Gastro', 7, 9.00, 101, '2027-12-31', '2026-09-01 08:00:00', 'แคปซูล', NULL, 'active'),
(8, 'Simethicone 80mg', 'Simethicone', 'Gastro', 8, 5.50, 104, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(9, 'ORS Sachet', 'Oral rehydration salts', 'Gastro', 9, 7.00, 107, '2027-12-31', '2026-09-01 08:00:00', 'ซอง', NULL, 'active'),
(10, 'Dextromethorphan Syrup', 'Dextromethorphan', 'Cold & Flu', 10, 55.00, 110, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(11, 'Guaifenesin Syrup', 'Guaifenesin', 'Cold & Flu', 11, 58.00, 113, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(12, 'Chlorpheniramine 4mg', 'Chlorpheniramine', 'Allergy', 12, 3.50, 116, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(13, 'Aspirin 81mg', 'Aspirin', 'Cardio', 13, 3.80, 119, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(14, 'Metformin 500mg', 'Metformin', 'Diabetes', 14, 4.50, 122, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(15, 'Amlodipine 5mg', 'Amlodipine', 'Hypertension', 15, 10.00, 125, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(16, 'Losartan 50mg', 'Losartan', 'Hypertension', 1, 7.50, 128, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(17, 'Atorvastatin 20mg', 'Atorvastatin', 'Cardio', 2, 10.00, 131, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(18, 'Clopidogrel 75mg', 'Clopidogrel', 'Cardio', 3, 12.00, 134, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(19, 'Azithromycin 250mg', 'Azithromycin', 'Antibiotic', 4, 14.00, 137, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(20, 'Ciprofloxacin 500mg', 'Ciprofloxacin', 'Antibiotic', 5, 16.00, 140, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(21, 'Doxycycline 100mg', 'Doxycycline', 'Antibiotic', 6, 12.00, 143, '2027-12-31', '2026-09-01 08:00:00', 'แคปซูล', NULL, 'active'),
(22, 'Diclofenac 25mg', 'Diclofenac', 'Pain Relief', 7, 6.50, 146, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(23, 'Mefenamic acid 500mg', 'Mefenamic acid', 'Pain Relief', 8, 8.50, 149, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(24, 'Domperidone 10mg', 'Domperidone', 'Gastro', 9, 6.00, 152, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(25, 'Loperamide 2mg', 'Loperamide', 'Gastro', 10, 5.50, 155, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(26, 'Fexofenadine 180mg', 'Fexofenadine', 'Allergy', 11, 10.00, 158, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(27, 'Salbutamol Inhaler', 'Salbutamol', 'Respiratory', 12, 180.00, 161, '2027-12-31', '2026-09-01 08:00:00', 'ชิ้น', NULL, 'active'),
(28, 'Budesonide Inhaler', 'Budesonide', 'Respiratory', 13, 320.00, 164, '2027-12-31', '2026-09-01 08:00:00', 'ชิ้น', NULL, 'active'),
(29, 'Ammonium chloride mixture', 'Ammonium chloride', 'Cold & Flu', 14, 65.00, 167, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(30, 'Nasal saline spray', 'Sodium chloride', 'Cold & Flu', 15, 95.00, 170, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(31, 'Hydrocortisone cream 1%', 'Hydrocortisone', 'Dermatology', 1, 85.00, 173, '2027-12-31', '2026-09-01 08:00:00', 'หลอด', NULL, 'active'),
(32, 'Clotrimazole cream 1%', 'Clotrimazole', 'Dermatology', 2, 95.00, 176, '2027-12-31', '2026-09-01 08:00:00', 'หลอด', NULL, 'active'),
(33, 'Mupirocin ointment 2%', 'Mupirocin', 'Dermatology', 3, 150.00, 179, '2027-12-31', '2026-09-01 08:00:00', 'หลอด', NULL, 'active'),
(34, 'Povidone iodine 10%', 'Povidone iodine', 'First Aid', 4, 70.00, 182, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(35, 'Alcohol 70%', 'Ethanol', 'First Aid', 5, 55.00, 185, '2027-12-31', '2026-09-01 08:00:00', 'ขวด', NULL, 'active'),
(36, 'Plaster bandage', 'Adhesive bandage', 'First Aid', 6, 6.00, 188, '2027-12-31', '2026-09-01 08:00:00', 'ชิ้น', NULL, 'active'),
(37, 'Calcium carbonate 500mg', 'Calcium carbonate', 'Supplement', 7, 6.50, 191, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(38, 'Zinc 20mg', 'Zinc', 'Supplement', 8, 7.00, 194, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(39, 'Vitamin B Complex', 'B vitamins', 'Vitamin', 9, 8.00, 197, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(40, 'Folic acid 5mg', 'Folic acid', 'Vitamin', 10, 5.00, 200, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active'),
(41, 'Dimenhydrinate 50mg', 'Dimenhydrinate', 'Motion Sickness', 1, 5.00, 100, '2027-12-31', '2026-09-01 08:00:00', 'เม็ด', NULL, 'active');

-- orders
INSERT INTO orders (order_id, customer_id, employee_id, order_date, total_amount, requireddate, shippeddate, status, payment_method, amount_received, amount_change, payment_status) VALUES
(1, 1, 1, '2026-09-01 10:00:00', 8.00, NULL, NULL, 'PAID', 'transfer', 8.00, 0.00, 'paid'),
(2, 2, 2, '2026-09-01 11:00:00', 19.50, NULL, NULL, 'PAID', 'cash', 1000.00, 980.50, 'paid'),
(3, 3, 3, '2026-09-02 12:00:00', 28.00, NULL, NULL, 'PAID', 'transfer', 28.00, 0.00, 'paid'),
(4, 4, 4, '2026-09-02 13:00:00', 5.00, NULL, NULL, 'PAID', 'cash', 1000.00, 995.00, 'paid'),
(5, 9999, 5, '2026-09-03 14:00:00', 9.00, NULL, NULL, 'PAID', 'transfer', 9.00, 0.00, 'paid'),
(6, 6, 1, '2026-09-03 15:00:00', 15.00, NULL, NULL, 'PAID', 'cash', 1000.00, 985.00, 'paid'),
(7, 7, 2, '2026-09-04 16:00:00', 36.00, NULL, NULL, 'PAID', 'transfer', 36.00, 0.00, 'paid'),
(8, 8, 3, '2026-09-04 09:00:00', 5.50, NULL, NULL, 'PAID', 'cash', 1000.00, 994.50, 'paid'),
(9, 9, 4, '2026-09-05 10:00:00', 14.00, NULL, NULL, 'PAID', 'transfer', 14.00, 0.00, 'paid'),
(10, 9999, 5, '2026-09-05 11:00:00', 165.00, NULL, NULL, 'PAID', 'cash', 1000.00, 835.00, 'paid'),
(11, 11, 1, '2026-09-06 12:00:00', 232.00, NULL, NULL, 'PAID', 'transfer', 232.00, 0.00, 'paid'),
(12, 12, 2, '2026-09-06 13:00:00', 3.50, NULL, NULL, 'PAID', 'cash', 1000.00, 996.50, 'paid'),
(13, 13, 3, '2026-09-07 14:00:00', 7.60, NULL, NULL, 'PAID', 'transfer', 7.60, 0.00, 'paid'),
(14, 14, 4, '2026-09-07 15:00:00', 13.50, NULL, NULL, 'PAID', 'cash', 1000.00, 986.50, 'paid'),
(15, 9999, 5, '2026-09-08 16:00:00', 40.00, NULL, NULL, 'PAID', 'transfer', 40.00, 0.00, 'paid'),
(16, 16, 1, '2026-09-08 09:00:00', 7.50, NULL, NULL, 'PAID', 'cash', 1000.00, 992.50, 'paid'),
(17, 17, 2, '2026-09-09 10:00:00', 20.00, NULL, NULL, 'PAID', 'transfer', 20.00, 0.00, 'paid'),
(18, 18, 3, '2026-09-09 11:00:00', 36.00, NULL, NULL, 'PAID', 'cash', 1000.00, 964.00, 'paid'),
(19, 19, 4, '2026-09-10 12:00:00', 56.00, NULL, NULL, 'PAID', 'transfer', 56.00, 0.00, 'paid'),
(20, 9999, 5, '2026-09-10 13:00:00', 16.00, NULL, NULL, 'PAID', 'cash', 1000.00, 984.00, 'paid'),
(21, 1, 1, '2026-09-11 14:00:00', 24.00, NULL, NULL, 'PAID', 'transfer', 24.00, 0.00, 'paid'),
(22, 2, 2, '2026-09-11 15:00:00', 19.50, NULL, NULL, 'PAID', 'cash', 1000.00, 980.50, 'paid'),
(23, 3, 3, '2026-09-12 16:00:00', 34.00, NULL, NULL, 'PAID', 'transfer', 34.00, 0.00, 'paid'),
(24, 4, 4, '2026-09-12 09:00:00', 6.00, NULL, NULL, 'PAID', 'cash', 1000.00, 994.00, 'paid'),
(25, 9999, 5, '2026-09-13 10:00:00', 11.00, NULL, NULL, 'PAID', 'transfer', 11.00, 0.00, 'paid'),
(26, 6, 1, '2026-09-13 11:00:00', 30.00, NULL, NULL, 'PAID', 'cash', 1000.00, 970.00, 'paid'),
(27, 7, 2, '2026-09-14 12:00:00', 720.00, NULL, NULL, 'PAID', 'transfer', 720.00, 0.00, 'paid'),
(28, 8, 3, '2026-09-14 13:00:00', 320.00, NULL, NULL, 'PAID', 'cash', 1000.00, 680.00, 'paid'),
(29, 9, 4, '2026-09-15 14:00:00', 130.00, NULL, NULL, 'PAID', 'transfer', 130.00, 0.00, 'paid'),
(30, 9999, 5, '2026-09-15 15:00:00', 285.00, NULL, NULL, 'PAID', 'cash', 1000.00, 715.00, 'paid'),
(31, 11, 1, '2026-09-16 16:00:00', 340.00, NULL, NULL, 'PAID', 'transfer', 340.00, 0.00, 'paid'),
(32, 12, 2, '2026-09-16 09:00:00', 95.00, NULL, NULL, 'PAID', 'cash', 1000.00, 905.00, 'paid'),
(33, 13, 3, '2026-09-17 10:00:00', 300.00, NULL, NULL, 'PAID', 'transfer', 300.00, 0.00, 'paid'),
(34, 14, 4, '2026-09-17 11:00:00', 210.00, NULL, NULL, 'PAID', 'cash', 1000.00, 790.00, 'paid'),
(35, 9999, 5, '2026-09-18 12:00:00', 220.00, NULL, NULL, 'PAID', 'transfer', 220.00, 0.00, 'paid'),
(36, 16, 1, '2026-09-18 13:00:00', 6.00, NULL, NULL, 'PAID', 'cash', 1000.00, 994.00, 'paid'),
(37, 17, 2, '2026-09-19 14:00:00', 13.00, NULL, NULL, 'PAID', 'transfer', 13.00, 0.00, 'paid'),
(38, 18, 3, '2026-09-19 15:00:00', 21.00, NULL, NULL, 'PAID', 'cash', 1000.00, 979.00, 'paid'),
(39, 19, 4, '2026-09-20 16:00:00', 32.00, NULL, NULL, 'PAID', 'transfer', 32.00, 0.00, 'paid'),
(40, 9999, 5, '2026-09-20 09:00:00', 5.00, NULL, NULL, 'PAID', 'cash', 1000.00, 995.00, 'paid'),
(41, 1, 1, '2026-09-21 10:00:00', 10.00, NULL, NULL, 'PAID', 'transfer', 10.00, 0.00, 'paid'),
(42, 2, 2, '2026-09-21 11:00:00', 12.00, NULL, NULL, 'PAID', 'cash', 1000.00, 988.00, 'paid'),
(43, 3, 3, '2026-09-22 12:00:00', 26.00, NULL, NULL, 'PAID', 'transfer', 26.00, 0.00, 'paid'),
(44, 4, 4, '2026-09-22 13:00:00', 7.00, NULL, NULL, 'PAID', 'cash', 1000.00, 993.00, 'paid'),
(45, 9999, 5, '2026-09-23 14:00:00', 10.00, NULL, NULL, 'PAID', 'transfer', 10.00, 0.00, 'paid'),
(46, 6, 1, '2026-09-23 15:00:00', 13.50, NULL, NULL, 'PAID', 'cash', 1000.00, 986.50, 'paid'),
(47, 7, 2, '2026-09-24 16:00:00', 20.00, NULL, NULL, 'PAID', 'transfer', 20.00, 0.00, 'paid'),
(48, 8, 3, '2026-09-24 09:00:00', 9.00, NULL, NULL, 'PAID', 'cash', 1000.00, 991.00, 'paid'),
(49, 9, 4, '2026-09-25 10:00:00', 11.00, NULL, NULL, 'PAID', 'transfer', 11.00, 0.00, 'paid'),
(50, 9999, 5, '2026-09-25 11:00:00', 21.00, NULL, NULL, 'PAID', 'cash', 1000.00, 979.00, 'paid'),
(51, 11, 1, '2026-09-26 12:00:00', 220.00, NULL, NULL, 'PAID', 'transfer', 220.00, 0.00, 'paid'),
(52, 12, 2, '2026-09-26 13:00:00', 58.00, NULL, NULL, 'PAID', 'cash', 1000.00, 942.00, 'paid'),
(53, 13, 3, '2026-09-27 14:00:00', 7.00, NULL, NULL, 'PAID', 'transfer', 7.00, 0.00, 'paid'),
(54, 14, 4, '2026-09-27 15:00:00', 11.40, NULL, NULL, 'PAID', 'cash', 1000.00, 988.60, 'paid'),
(55, 9999, 5, '2026-09-28 16:00:00', 18.00, NULL, NULL, 'PAID', 'transfer', 18.00, 0.00, 'paid'),
(56, 16, 1, '2026-09-28 09:00:00', 10.00, NULL, NULL, 'PAID', 'cash', 1000.00, 990.00, 'paid'),
(57, 17, 2, '2026-09-29 10:00:00', 15.00, NULL, NULL, 'PAID', 'transfer', 15.00, 0.00, 'paid'),
(58, 18, 3, '2026-09-29 11:00:00', 30.00, NULL, NULL, 'PAID', 'cash', 1000.00, 970.00, 'paid'),
(59, 19, 4, '2026-09-30 12:00:00', 48.00, NULL, NULL, 'PAID', 'transfer', 48.00, 0.00, 'paid'),
(60, 9999, 5, '2026-09-30 13:00:00', 14.00, NULL, NULL, 'PAID', 'cash', 1000.00, 986.00, 'paid');

-- order_items
INSERT INTO order_items (order_item_id, order_id, medicine_id, quantity, unit_price) VALUES
(1, 1, 1, 2, 4.00),
(2, 2, 2, 3, 6.50),
(3, 3, 3, 4, 7.00),
(4, 4, 4, 1, 5.00),
(5, 5, 5, 2, 4.50),
(6, 6, 6, 3, 5.00),
(7, 7, 7, 4, 9.00),
(8, 8, 8, 1, 5.50),
(9, 9, 9, 2, 7.00),
(10, 10, 10, 3, 55.00),
(11, 11, 11, 4, 58.00),
(12, 12, 12, 1, 3.50),
(13, 13, 13, 2, 3.80),
(14, 14, 14, 3, 4.50),
(15, 15, 15, 4, 10.00),
(16, 16, 16, 1, 7.50),
(17, 17, 17, 2, 10.00),
(18, 18, 18, 3, 12.00),
(19, 19, 19, 4, 14.00),
(20, 20, 20, 1, 16.00),
(21, 21, 21, 2, 12.00),
(22, 22, 22, 3, 6.50),
(23, 23, 23, 4, 8.50),
(24, 24, 24, 1, 6.00),
(25, 25, 25, 2, 5.50),
(26, 26, 26, 3, 10.00),
(27, 27, 27, 4, 180.00),
(28, 28, 28, 1, 320.00),
(29, 29, 29, 2, 65.00),
(30, 30, 30, 3, 95.00),
(31, 31, 31, 4, 85.00),
(32, 32, 32, 1, 95.00),
(33, 33, 33, 2, 150.00),
(34, 34, 34, 3, 70.00),
(35, 35, 35, 4, 55.00),
(36, 36, 36, 1, 6.00),
(37, 37, 37, 2, 6.50),
(38, 38, 38, 3, 7.00),
(39, 39, 39, 4, 8.00),
(40, 40, 40, 1, 5.00),
(41, 41, 41, 2, 5.00),
(42, 42, 1, 3, 4.00),
(43, 43, 2, 4, 6.50),
(44, 44, 3, 1, 7.00),
(45, 45, 4, 2, 5.00),
(46, 46, 5, 3, 4.50),
(47, 47, 6, 4, 5.00),
(48, 48, 7, 1, 9.00),
(49, 49, 8, 2, 5.50),
(50, 50, 9, 3, 7.00),
(51, 51, 10, 4, 55.00),
(52, 52, 11, 1, 58.00),
(53, 53, 12, 2, 3.50),
(54, 54, 13, 3, 3.80),
(55, 55, 14, 4, 4.50),
(56, 56, 15, 1, 10.00),
(57, 57, 16, 2, 7.50),
(58, 58, 17, 3, 10.00),
(59, 59, 18, 4, 12.00),
(60, 60, 19, 1, 14.00);

-- พนักงาน "Admin" สำหรับบันทึกการขาย (ไม่มีระบบ login) — ตรงกับ DEFAULT_EMPLOYEE_ID ใน db.php
INSERT INTO employees (employee_id, name, position, status) VALUES (21, 'Admin', 'แอดมิน', 'active');

-- ตั้งค่า sequence ให้รันต่อจาก ID สูงสุด
SELECT setval(pg_get_serial_sequence('customers','customer_id'), (SELECT MAX(customer_id) FROM customers));
SELECT setval(pg_get_serial_sequence('employees','employee_id'), (SELECT MAX(employee_id) FROM employees));
SELECT setval(pg_get_serial_sequence('expired_logs','log_id'), (SELECT MAX(log_id) FROM expired_logs));
SELECT setval(pg_get_serial_sequence('suppliers','supplier_id'), (SELECT MAX(supplier_id) FROM suppliers));
SELECT setval(pg_get_serial_sequence('medicines','medicine_id'), (SELECT MAX(medicine_id) FROM medicines));
SELECT setval(pg_get_serial_sequence('orders','order_id'), (SELECT MAX(order_id) FROM orders));
SELECT setval(pg_get_serial_sequence('order_items','order_item_id'), (SELECT MAX(order_item_id) FROM order_items));

-- ปิดการเข้าถึงผ่าน Supabase REST API (anon key) — แอปเชื่อมต่อผ่าน Postgres โดยตรงอยู่แล้ว
ALTER TABLE customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE employees ENABLE ROW LEVEL SECURITY;
ALTER TABLE expired_logs ENABLE ROW LEVEL SECURITY;
ALTER TABLE suppliers ENABLE ROW LEVEL SECURITY;
ALTER TABLE medicines ENABLE ROW LEVEL SECURITY;
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE order_items ENABLE ROW LEVEL SECURITY;

COMMIT;

-- ============================================================
-- รูปยา: กู้จาก phpMyAdmin dump เดิม (pharmacy_system, 5 เม.ย. 2026)
-- ใส่ไว้ท้ายไฟล์เพื่อให้รันไฟล์นี้ซ้ำ (reset ข้อมูล) แล้วรูปยาไม่หาย
-- ============================================================
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.AkYPk-WOVm8IWjwhkSmq3gHaE6?w=266&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 1 AND name = 'Paracetamol 500mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.7b_OgiPsC45t3IOt3woGvQHaFP?w=268&h=190&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 2 AND name = 'Amoxicillin 250mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.FzVOfvlL3BKMgzZ5IuTGlwHaHa?w=193&h=193&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 3 AND name = 'Ibuprofen 400mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.HaPmwugrJvNLeLMF-N6ulgHaHa?w=199&h=199&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 4 AND name = 'Vitamin C 500mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.MMU4cLhdbv6YTPDeTeEwjwHaHa?w=175&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 5 AND name = 'Cetirizine 10mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.1WszR5Dneu73E36CKxPzAAHaHa?w=198&h=198&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 6 AND name = 'Loratadine 10mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.Z8KdOJ1Ik6dMxfj9RXktCAHaFd?w=268&h=197&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 7 AND name = 'Omeprazole 20mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.8DHvBmFfWuullr5oXDjiywHaHa?w=169&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 8 AND name = 'Simethicone 80mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.f81_lYZgQDCdeI25QrT9mAHaHa?w=184&h=184&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 9 AND name = 'ORS Sachet';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.RN3kBd3ibqIAogJl_My-zQHaF-?w=248&h=200&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 10 AND name = 'Dextromethorphan Syrup';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.5G156P35uJxwojvUp874-gHaHa?w=185&h=185&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 11 AND name = 'Guaifenesin Syrup';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.-EBv2-lIFPmAHVZTZoGiUwHaEO?w=283&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 12 AND name = 'Chlorpheniramine 4mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.rZMQLzzlkxIsNJC3T_S8OwHaHa?w=179&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 13 AND name = 'Aspirin 81mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.QIV7WcQbF6wuRQV-YRRQ7AHaHa?w=168&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 14 AND name = 'Metformin 500mg';
UPDATE medicines SET image_url = 'https://5.imimg.com/data5/SELLER/Default/2023/7/326770036/BL/UA/BR/135658020/amlodipine-tablets-ip-5-mg-1000x1000.jpg' WHERE medicine_id = 15 AND name = 'Amlodipine 5mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.FCOIg_WCc3VBBNdc5dcATAHaFF?w=252&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 16 AND name = 'Losartan 50mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.zDHjGOz3EiyIDlGNjbaq-gHaFg?w=271&h=202&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 17 AND name = 'Atorvastatin 20mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.On0UDizH66RajpzNCZhDCwHaFj?w=225&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 18 AND name = 'Clopidogrel 75mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.hpi0Le9y9l6QOLQhcyvjFwHaHa?w=202&h=202&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 19 AND name = 'Azithromycin 250mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP._P00qg_gG7sQO4rnCJT-JgHaEK?w=295&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 20 AND name = 'Ciprofloxacin 500mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.IFagLozY10EwbO57ovrIbgHaHa?w=187&h=187&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 21 AND name = 'Doxycycline 100mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.cOqM7W6Tlw03A_5COwHn3AHaEP?w=289&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 22 AND name = 'Diclofenac 25mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.5U2fVzUrvoB6evCZM4D7ZwHaF7?w=269&h=215&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 23 AND name = 'Mefenamic acid 500mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.9rtUJdYH3v_n7TqP5_2KbwHaE1?w=281&h=184&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 24 AND name = 'Domperidone 10mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.LxtRHlhKkSg7ogoL5a7wFwHaHa?w=178&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 25 AND name = 'Loperamide 2mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.-ziZMgVagDRsnIv6m2OVbgHaHa?w=195&h=195&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 26 AND name = 'Fexofenadine 180mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.3_xNeaFWBwMTQ2jMLgs5IQHaHa?w=195&h=195&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 27 AND name = 'Salbutamol Inhaler';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.sFkHAWhkaVS2f4ynC2_6dwHaFj?w=230&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 28 AND name = 'Budesonide Inhaler';
UPDATE medicines SET image_url = 'https://m.media-amazon.com/images/I/61rY4Xmt84L.jpg' WHERE medicine_id = 29 AND name = 'Ammonium chloride mixture';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.ZOFt1gMRj7TJA8Ys9TQSMQHaHa?w=196&h=196&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 30 AND name = 'Nasal saline spray';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.Zb4V12MiJ80iIXEBMQYEbQHaHa?w=170&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 31 AND name = 'Hydrocortisone cream 1%';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.xnSndxP6lk6-fw0aiTtQeAHaHa?w=188&h=188&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 32 AND name = 'Clotrimazole cream 1%';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.e4CrSY71zXlAvEsyTYY3_gHaFj?w=231&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 33 AND name = 'Mupirocin ointment 2%';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.HnBownKZvl6VNic4P8VR5AHaGG?w=231&h=191&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 34 AND name = 'Povidone iodine 10%';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.Y_IvEc8nKpOw2ase8KEfwAHaHa?w=202&h=202&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 36 AND name = 'Plaster bandage';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.zeJYuZfvfg5JYIMfQSKzDAHaHa?w=194&h=193&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 37 AND name = 'Calcium carbonate 500mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.2s-0Dh1sDKcoHgzoUw3O8QHaGb?w=221&h=192&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 38 AND name = 'Zinc 20mg';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.8gYR1Njd8EnPyKdP6kPETgHaHa?w=164&h=180&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 39 AND name = 'Vitamin B Complex';
UPDATE medicines SET image_url = 'https://th.bing.com/th/id/OIP.BM_xLkr7oE4jN97qG3A1agHaEY?w=312&h=185&c=7&r=0&o=7&dpr=1.3&pid=1.7&rm=3' WHERE medicine_id = 40 AND name = 'Folic acid 5mg';
-- id 35: ชื่อใน dump เดิมคือ 'Alcohol 72%' ส่วนปัจจุบันคือ 'Alcohol 70%' — สินค้าเดียวกัน ใช้รูปเดิม
UPDATE medicines SET image_url = 'https://upload.wikimedia.org/wikipedia/commons/b/b7/Rubbing_alcohol.JPG' WHERE medicine_id = 35;
-- id 41 Dimenhydrinate 50mg: dump เดิมเป็นยาคนละตัว ('ยาแก้เหน็บชา') จึงไม่ใส่รูป
