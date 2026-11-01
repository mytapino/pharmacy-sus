# REDESIGN_NOTES.md — HTS Final (จาก SUS สู่ Redesign)

Branch: `redesign/hts-final` · Before = `main` (commit `72ea683`, ระบบที่ใช้ทดสอบ SUS)

## สิ่งที่แก้ (2 Finding จาก FORM 6 Quick Win)

| Finding | Rule | ไฟล์ | สิ่งที่แก้ |
|---|---|---|---|
| F-01 หาจุดเพิ่มยาเข้าบิล/ปุ่มดำเนินการขายไม่เจอ (A, B, E, F) | **R-06** (ใหม่) | `pages/sell_medicine.php`, `pages/payment.php`, `pages/process_sale.php` | เลือกยาแล้วเข้าบิลทันที · แถบขั้นตอน 1–3 · Empty State บิลว่าง · ปุ่มบอกจำนวนรายการ + ยอดรวม · ตัวเลือกยาแสดงชื่อสามัญ/ราคา/คงเหลือ · ปุ่ม − / + · ช่องค้นหาโฟกัสอัตโนมัติ · หน้ารับเงินบอกเงินที่ขาด · ย้ายเข้า token/Topbar ของ design.md · กันบิลว่างฝั่งเซิร์ฟเวอร์ |
| F-04 ติดที่ช่องวันหมดอายุ และไม่แน่ใจว่ากดบันทึกแล้วไปต่อไหม (A, B, E) | **R-04** (เพิ่มข้อกำหนด) | `pages/add_medicine_form.php`, `pages/medicines.php` | Loading State + กันกดซ้ำ · แถบยืนยันและแถวยาใหม่บนสุด (`?added=ID`) · แสดงวันที่ที่เลือกเป็นภาษาไทย · `*` ช่องจำเป็น + Error ใต้ช่อง · เพิ่มช่องชื่อสามัญ · datalist หมวดหมู่/หน่วย · ย้ายเข้า token/Topbar |

ไม่มีการแก้ schema ฐานข้อมูล (`generic_name` มีอยู่แล้วในตาราง `medicines`)

## วิธีเตรียม Before–After สำหรับอัดคลิป (รันสดจาก URL)

1. **อย่า merge เข้า `main` ก่อนอัดคลิป** — `https://pharmacy-sus.vercel.app/` คือ Before
2. Push branch นี้ขึ้น GitHub → Vercel จะสร้าง Preview URL ของ branch ให้อัตโนมัติ (ดูในแท็บ Deployments) → ใช้เป็น After
   - Preview ใช้ `DATABASE_URL` เดียวกันได้ ถ้าตั้ง Environment Variable ให้ครอบคลุม Preview ด้วย (Settings → Environment Variables → เลือก Preview)
3. ก่อนอัด รัน `database/supabase_schema.sql` ใน Supabase อีกครั้งเพื่อ reset ข้อมูล (ไม่ให้ Testmed 500mg จากการซ้อมค้างอยู่)
4. อัดเสร็จแล้วค่อย merge `redesign/hts-final` → `main`

## ทดสอบแล้ว (local PHP 8.3 + PostgreSQL 16, schema เดียวกับ Supabase)

- Task 1: Paracetamol 500mg × 2 + Cetirizine 10mg × 1 → ปุ่มแสดง `2 รายการ · ฿12.50` → รับเงิน 100 → เงินทอน ฿87.50 → ใบเสร็จ · สต็อกลดถูกต้อง (83→81, 95→94)
- Task 3: Testmed 500mg / Test Medicine / Pain Relief / 10 บาท / 50 เม็ด / 31-12-2028 → บันทึกแล้วกลับหน้ารายการยา เห็นแถบยืนยันและแถวยาใหม่บนสุด
- ส่งฟอร์มเพิ่มยาว่าง → Error ใต้ทุกช่อง `*` · รับเงินไม่พอ → ข้อความบอกจำนวนที่ขาด และปุ่มกดไม่ได้
