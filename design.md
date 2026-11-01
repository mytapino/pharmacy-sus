# design.md
## Pharmacy System — Design System Final

> ฉบับ Final หลังทดลอง AI Re-design 2 หน้าจอและปรับปรุงจาก `design_v1.md`  
> ระบบ: Pharmacy POS / ERP Back-office  
> หน้าจอที่ใช้ทดลอง:
> 1. Dashboard
> 2. Supplier Management — Contextual Supplier Comparison State
>
> **Design Direction:** Reliable / Clean / Efficient  
> **หลักสำคัญ:** ใช้ Design Token และ Pattern เดียวกันทั้งระบบ ลด Visual Noise และทำให้ผู้ใช้ Scan ข้อมูลได้เร็ว

---

# 1. Color Tokens

## 1.1 Moodboard Direction

**Keywords:** Reliable / Clean / Efficient  
**ความหมาย:** น่าเชื่อถือ / สะอาดเป็นระบบ / ทำงานได้รวดเร็ว

แนวทางสีพัฒนาต่อจากภาพลักษณ์เดิมของ Pharmacy System ที่ใช้สีเขียวและสีขาวเป็นหลัก โดยปรับโทนสีเขียวให้มีความหม่นและสุขุมขึ้น เพื่อลดความแสบตาและทำให้ระบบดูเป็นมืออาชีพมากขึ้น

พื้นหลังและ Surface ใช้สีขาวกับเขียวอมเทาอ่อนเพื่อช่วยลดความหนาแน่นของข้อมูล ส่วนสี Warning, Danger, Success และ Information ใช้เฉพาะเมื่อต้องสื่อความหมายของสถานะ ไม่ใช้เป็นสีตกแต่งทั่วไป

### Color Strategy
- 80–90% ของพื้นที่หน้าจอใช้ White / Off-white / Muted Teal
- Primary Action ใช้ Teal เพียงชุดเดียว
- สี Blue ใช้กับ Information, Link และ Graph
- สี Amber ใช้กับสถานะที่ “ต้องติดตาม”
- สี Red ใช้กับสถานะ “Critical / Error”
- สี Green รองใช้กับ Success / Normal Status
- ห้ามใช้ Gradient
- ห้ามทำ KPI Card คนละสี
- ห้ามใช้ Warning / Danger / Success เป็นพื้น Card ขนาดใหญ่เพื่อการตกแต่ง
- ปุ่มที่มีหน้าที่เดียวกันต้องใช้สีเดียวกันทุกหน้าจอ
- ใช้สีเพื่อสื่อความหมาย มากกว่าใช้เพื่อสร้างความเด่น

## 1.2 Core Color Tokens

| Token | HEX | Usage | Meaning / Moodboard Connection |
|---|---|---|---|
| `primary-600` | `#2F6F68` | Primary Button, Active Navigation, Functional Icon, Selected Action | Reliable — สุขุม น่าเชื่อถือ และเป็นสีหลักของระบบ |
| `primary-700` | `#245A55` | Primary Hover, Strong Emphasis | Reliable — เพิ่มน้ำหนักในสถานะที่ต้องการความชัด |
| `primary-50` | `#EAF4F2` | Selected Background, Soft Highlight, Row Hover | Clean — แยกพื้นที่โดยไม่เพิ่มสีสด |
| `background-page` | `#F6F8F7` | Page Background, Table Header | Clean — ลดความแข็งของพื้นขาวและช่วยให้ Card แยกจากพื้น |
| `surface` | `#FFFFFF` | Card, Table, Form, Drawer, Modal | Clean — สะอาด อ่านง่าย |
| `text-primary` | `#25312F` | Heading, Main Body Text, KPI | Reliable — ให้ความคมชัดโดยไม่ใช้ดำสนิท |
| `text-secondary` | `#66726F` | Label, Metadata, Supporting Text, Secondary Icon | Clean — ลดลำดับความสำคัญของข้อมูลรอง |
| `border-default` | `#DCE4E1` | Divider, Table Border, Input Border | Clean — แยกโครงสร้างแบบเบา |
| `info-600` | `#4F7390` | Information, Link, Chart Secondary | Efficient — ช่วยระบุข้อมูลและ Action รอง |
| `warning-600` | `#A06A2B` | Low Stock, Near Expiry | Efficient — บอกข้อมูลที่ควรติดตาม |
| `danger-600` | `#B55252` | Error, Out of Stock, Expired | Efficient — บอกสิ่งที่ต้องจัดการทันที |
| `success-600` | `#4F806B` | Success, Normal Status | Efficient — บอกสถานะปกติหรือสำเร็จ |

## 1.3 Contrast Rules

ข้อความทั่วไปต้องผ่าน WCAG AA อย่างน้อย `4.5:1`

| Foreground | Background | Contrast | Result |
|---|---|---:|---|
| `#FFFFFF` | `#2F6F68` | 5.84:1 | Pass AA |
| `#FFFFFF` | `#245A55` | 7.87:1 | Pass AAA |
| `#25312F` | `#FFFFFF` | 13.47:1 | Pass AAA |
| `#66726F` | `#FFFFFF` | 5.00:1 | Pass AA |
| `#4F7390` | `#FFFFFF` | 5.01:1 | Pass AA |
| `#A06A2B` | `#FFFFFF` | 4.57:1 | Pass AA |
| `#B55252` | `#FFFFFF` | 4.89:1 | Pass AA |
| `#4F806B` | `#FFFFFF` | 4.53:1 | Pass AA |
| `#25312F` | `#EAF4F2` | 12.01:1 | Pass AAA |
| `#25312F` | `#F6F8F7` | 12.63:1 | Pass AAA |
| `#66726F` | `#F6F8F7` | 4.69:1 | Pass AA |

**ข้อห้าม:** `text-secondary #66726F` ห้ามใช้เป็นข้อความ Body หลักบน `primary-50 #EAF4F2` เพราะ Contrast ต่ำกว่า 4.5:1 ให้ใช้ `text-primary #25312F` แทน

## 1.4 Semantic Color Restriction

สี Semantic มีไว้เพื่อสื่อสถานะเท่านั้น:

- Warning = Low Stock / Near Expiry
- Danger = Error / Expired / Out of Stock
- Success = Completed / Normal
- Info = Informational State

**ห้าม**
- ใช้ Semantic Color เพื่อทำ Card คนละสี
- ใช้สีเพื่อ Decoration โดยไม่มีความหมาย
- ใช้ Icon แต่ละ Section คนละสีเพื่อสร้างความเด่น

---

# 2. Typography

## 2.1 Font Family

**Primary Font:** `Noto Sans Thai`

ใช้ฟอนต์เดียวทั้งภาษาไทยและภาษาอังกฤษเพื่อลดความซับซ้อนของระบบ และรักษาความสม่ำเสมอของ UI

## 2.2 Type Scale

| Token | Font Size | Line Height | Weight | Usage |
|---|---:|---:|---:|---|
| `display-sm` | 28px | 36px | 700 | KPI Value / Important Number |
| `heading-1` | 24px | 32px | 700 | Page Title |
| `heading-2` | 20px | 28px | 600 | Section Heading |
| `heading-3` | 16px | 24px | 600 | Card / Table / Drawer Section Heading |
| `body` | 14px | 22px | 400 | General Content |
| `body-medium` | 14px | 22px | 500 | Important Label / Emphasis |
| `caption` | 12px | 18px | 400 | Metadata / Helper Text |
| `button` | 14px | 20px | 500 | Button / Text Action |

## 2.3 Visual Hierarchy Rules

1. KPI ใช้ขนาดตัวอักษรและ Weight สร้างความเด่น ไม่ใช้พื้นสีสด
2. Page Title ต้องเด่นกว่า Section Heading อย่างชัดเจน
3. Section Heading ใช้ `heading-2`
4. ชื่อ Card/Table/Drawer Section ใช้ `heading-3`
5. Body Text ใช้ `body`
6. Metadata และ Helper Text ใช้ `caption`
7. ใช้ Weight จริงไม่เกิน 4 ระดับ: `400 / 500 / 600 / 700`
8. ห้ามใช้ Bold กับข้อมูลทุกระดับ เพราะทำให้ Visual Hierarchy หาย
9. ใช้ `text-primary` กับข้อมูลหลัก และ `text-secondary` กับข้อมูลรอง
10. ใช้ Whitespace ร่วมกับ Typography เพื่อสร้างลำดับสายตา
11. Functional Icon ใช้เสริมการ Scan แต่ไม่แทนข้อความ Label

---

# 3. Spacing & Layout

## 3.1 Grid System

- Design Frame: `1440px`
- Content Max Width: `1280px`
- Grid: `12 Columns`
- Page Horizontal Padding: `32px`
- Section Gap: `32px`
- Standard Component Gap: `16px`
- Card Internal Padding: `24px`

## 3.2 Spacing Scale

ใช้ระบบ 8px Grid เท่านั้น

`8 / 16 / 24 / 32 / 40 / 48`

### Rules
- ห้ามใช้ spacing ที่ไม่ใช่ทวีคูณของ 8
- Related elements ใช้ Gap ขั้นต่ำ `8px`
- Component ภายในกลุ่มเดียวกันใช้ `16px`
- Section หลักแยกจากกันอย่างน้อย `32px`
- Card Padding หลักใช้ `24px`

## 3.3 Radius System

| Token | Radius | Usage |
|---|---:|---|
| `radius-sm` | 6px | Badge, Small Status Element |
| `radius-md` | 8px | Button, Input, Select, Product Thumbnail |
| `radius-lg` | 12px | Card, Panel |

### Radius Rules
- Card หลักใช้ Radius สูงสุด `12px`
- ห้ามใช้ Card Radius 20–24px
- หลีกเลี่ยง Pill Shape เว้นแต่เป็น Compact Filter หรือ Status ที่จำเป็น
- Input และ Button ใช้ Radius `8px`
- Badge ใช้ Radius `6px`
- ความโค้งต้องสม่ำเสมอ ไม่สร้างหลายระดับโดยไม่มีเหตุผล

## 3.4 Container Rules

1. หนึ่ง Section ใช้ Visual Container หลักไม่เกิน 1 ชั้น
2. หลีกเลี่ยง Card ซ้อน Card
3. ใช้ Border ก่อน Shadow
4. Card ปกติไม่ใช้ Shadow
5. Shadow อนุญาตเฉพาะ Floating Layer เช่น Modal / Dropdown / Drawer Overlay Context
6. Table ไม่ต้องครอบด้วย Card หลายชั้น
7. สีพื้นหลักของ Card คือ `surface #FFFFFF`
8. ใช้ Divider และ Alignment ก่อนสร้าง Container เพิ่ม

## 3.5 Dashboard Information Hierarchy

ลำดับเนื้อหาจากสำคัญมากไปน้อย:

### Level 1 — Business Overview
- ยอดขาย
- จำนวนบิล
- ยอดเฉลี่ยต่อบิล
- จำนวนลูกค้า

KPI ทั้ง 4 รายการ:
- อยู่ใน Container เดียว
- แบ่งเป็น 4 Columns
- ใช้ Divider เบา
- ใช้ Functional Icon ขนาดเล็กใกล้ Label
- ห้ามทำ Card 4 สี

### Level 2 — Sales Trend
- กราฟแนวโน้มยอดขาย
- ตัวกรองช่วงเวลา เช่น วันนี้ / 7 วัน / 30 วัน / เลือกเดือน
- หัวข้อสามารถใช้ Functional Icon เพื่อช่วย Scan

### Level 3 — Operational Alerts
- สินค้าใกล้หมดสต็อก
- ยาใกล้หมดอายุ

Desktop:
- แสดงเป็น 2 Columns
- แต่ละฝั่งเป็น Compact List
- ห้ามสร้าง Mini Card ทุกสินค้า
- Default แสดง `5` รายการที่เร่งด่วนที่สุด
- มี Action `ดูทั้งหมด 10 รายการ` เพื่อ Expand
- Expanded State ต้องสามารถตรวจ R-01 / R-02 ได้ครบ `10/10`

### Level 4 — Recent Sales
- ตารางรายการขายล่าสุด
- ใช้ Text/Icon Action ก่อน Solid Button ใน Row

### Level 5 — Staff Status
- พนักงานที่กำลังปฏิบัติงาน
- แสดงเป็น Status Row / Small Text
- ห้ามใช้ Card ใหญ่
- ต้องมี Visual Weight ต่ำกว่า KPI และ Operational Alerts

## 3.6 Supplier Management — Contextual Comparison Layout

หน้าจอนี้เปิดจาก:

**Dashboard → สินค้าใกล้หมดสต็อก → ดูซัพพลายเออร์**

หน้าที่คือช่วยผู้ใช้ดูเฉพาะ Supplier ที่ร้านเคยซื้อสินค้าที่เลือก และเปรียบเทียบข้อมูลจากประวัติการซื้อ

ลำดับ Layout:

1. Page Header
2. Product Context
3. Compact Supplier Summary
4. Price Disclaimer
5. Supplier Comparison Table
6. Side Drawer เมื่อเปิดดูรายละเอียด

### Product Context
ต้องแสดง:
- รูปสินค้า / Thumbnail
- Product Name
- Brand
- Strength
- Packaging
- Current Stock

Product Context ด้านบนถือเป็น **Single Source of Product Context** ของหน้า

### Supplier Table
แสดงเฉพาะ Supplier ที่มี Purchase History ของสินค้าที่เลือก

Table ต้องช่วยเปรียบเทียบ:
- Supplier
- Latest Purchase Price
- Last Purchase Date
- Last Purchase Quantity
- Purchase Condition
- Contact
- Detail Action

### Context Deduplication
เมื่อ Product Context ด้านบนชัดเจน:
- ไม่ต้องแสดง Product Name / Brand / Packaging ซ้ำใต้ชื่อ Supplier ทุก Row
- ข้อมูลสินค้าสามารถแสดงซ้ำใน Drawer ได้ เพราะ Drawer เป็น Context แยกจาก Main Table

---

# 4. Components & States

## 4.1 Button

### Base Specification
- Height: `40px`
- Horizontal Padding: `16px`
- Radius: `8px`
- Font: `Noto Sans Thai`
- Font Size: `14px`
- Line Height: `20px`
- Weight: `500`

### Variants
1. Primary
2. Secondary
3. Ghost / Text Action

### States

| State | Primary | Secondary / Ghost |
|---|---|---|
| Default | `#2F6F68` + White Text | Surface/Transparent + `text-primary` หรือ `primary-700` |
| Hover | `#245A55` | Soft Teal / Border Primary |
| Focus | Primary Focus Ring | Primary Focus Ring |
| Disabled | Neutral Background + Muted Text | Muted Text + No Hover |
| Loading | รักษาขนาดเดิม + Loading Message/Indicator | รักษาขนาดเดิม + Loading Message/Indicator |

### Button Rules
- Primary Action หลักต่อ Section ควรมีไม่เกิน 1 ปุ่ม
- ห้ามสร้างปุ่มฟ้า / ส้ม / เหลืองขึ้นมาเอง
- Danger Action ใช้เฉพาะ destructive action จริง
- Table Row Action ให้ใช้ Text/Icon Action ก่อน Solid Button หากไม่ใช่ Primary Action
- Loading State ต้องไม่ทำให้ Layout กระโดด

## 4.2 Input / Select

### Base Specification
- Height: `40px`
- Radius: `8px`
- Background: `#FFFFFF`
- Border: `1px solid #DCE4E1`
- Horizontal Padding: `16px`
- Font Size: `14px`

### States

| State | Specification |
|---|---|
| Default | Border `#DCE4E1`, White Background |
| Hover | Border เข้มขึ้นเล็กน้อยใน Neutral/Teal Family |
| Focus | Primary Border + Subtle Focus Ring |
| Disabled | Background `#F6F8F7`, Muted Text |
| Error | Border `#B55252` + Error Message ใต้ Field |

### Input Rules
- Error State ห้ามใช้พื้นแดงเต็ม Input
- Error Message ต้องบอกสาเหตุหรือสิ่งที่ผู้ใช้ควรทำต่อ
- Label ต้องแสดงตลอด ไม่ใช้ Placeholder แทน Label หลัก

## 4.3 Card / Panel

- Background: `#FFFFFF`
- Radius: `12px`
- Border: `1px solid #DCE4E1`
- Padding: `24px`
- Default Shadow: None
- ห้าม Card ซ้อน Card หากสามารถใช้ Divider/List ได้
- ห้ามใช้พื้นสีสดเพื่อแยกประเภท Card

## 4.4 Table

- Header Background: `#F6F8F7`
- Row Height: `48–56px`
- Divider: `1px solid #DCE4E1`
- Row Hover: `primary-50` หรือ Soft Neutral
- Text: `14px`
- Header Weight: `600`
- Action Column ชิดขวา
- ตัวเลขควรจัด Alignment ให้เปรียบเทียบง่าย
- หาก Table มี Context เดียวกันทั้งหน้า ห้ามแสดงข้อมูล Context ซ้ำโดยไม่จำเป็น

## 4.5 Badge / Status

- Radius: `6px`
- ใช้ Soft Background + Dark Text
- ห้ามใช้สีสดเต็มพื้นที่ขนาดใหญ่

Semantic usage:
- Warning = Low Stock / Near Expiry
- Danger = Out of Stock / Expired / Error
- Success = Normal / Completed
- Info = Informational State

## 4.6 Functional Icon

Functional Icon มีไว้เพื่อช่วย Scan และสร้าง Visual Landmark

### Specification
- Size: `18–20px`
- Style: Outline / Minimal
- Stroke: บางและสม่ำเสมอ
- Position: ใกล้ Heading หรือ Label ที่เกี่ยวข้อง
- Color: `primary-600`, `primary-700` หรือ `text-secondary`

### ใช้กับ
- KPI Label
- Section Heading
- Page Title เมื่อช่วยระบุประเภทหน้า
- Compact Summary
- Drawer Section Heading

### ห้าม
- Emoji
- Icon Background หลายสี
- Icon Card ขนาดใหญ่
- Decorative Icon ที่ไม่มีความหมาย
- ใช้สีคนละชุดในแต่ละ Section เพื่อสร้างความเด่น

## 4.7 Expandable Compact List

ใช้กับรายการที่ต้องมีข้อมูลครบ แต่ไม่ควรแสดงทั้งหมดใน Default Dashboard

### Pattern
- Default: แสดง 5 รายการ
- Expanded: แสดงครบ 10 รายการ
- Toggle: ระบุจำนวน เช่น `ดูทั้งหมด 10 รายการ`
- Collapse: ใช้ข้อความที่บอกผลลัพธ์ เช่น `ย่อเหลือ 5 รายการ`

### Rules
- ห้ามลบ Dataset เพื่อทำให้หน้า Short
- R-01 / R-02 ต้องตรวจได้ครบจาก Expanded State
- ใช้ Divider ระหว่าง Row
- ห้ามสร้าง Mini Card ทุก Row

## 4.8 Compact Summary

ใช้กับ Summary `2–4` ค่า ที่ต้อง Scan เร็วแต่ไม่ใช่ KPI หลักของหน้า

### Structure
- Functional Icon
- Label
- Value

### Layout
- Desktop: อยู่ใน Row เดียวกัน
- ใช้ Divider / Whitespace แบ่งกลุ่ม
- ไม่ทำเป็น KPI Card ใหญ่หลายใบ
- ใช้ `16px` หรือ `24px` Padding ตามพื้นที่

### Example
- เคยซื้อจาก `3 ซัพพลายเออร์`
- ราคาซื้อล่าสุดต่ำสุด `฿49.50`
- อัปเดตล่าสุด `12 สิงหาคม 2569`

## 4.9 Product Context Component

ใช้เมื่อผู้ใช้เปิดหน้ารายละเอียดจาก Item หนึ่งในหน้าก่อนหน้า

### Required Fields
- Product Image / Thumbnail
- Product Name
- Brand
- Strength
- Packaging
- Current Stock

### Style
- Surface `#FFFFFF`
- Border `#DCE4E1`
- Radius `12px`
- Padding `24px`
- Warning Color ใช้เฉพาะ Stock ที่ต้องติดตาม

### Rule
Product Context ต้องเป็นจุดอ้างอิงหลักของสินค้าที่กำลังทำงานอยู่ และช่วยลดการแสดงข้อมูลซ้ำใน Section อื่น

## 4.10 Side Drawer

ใช้เมื่อผู้ใช้ต้องการดูรายละเอียดรองโดยไม่ออกจากหน้าหลัก

### Use Case
- Supplier Information
- Contact Information
- Purchase History
- Product Detail ที่เกี่ยวข้องกับ Supplier รายที่เลือก

### Specification
- เปิดจากด้านขวา
- Surface `#FFFFFF`
- Border Left `border-default`
- มี Backdrop
- มี Close Button ชัดเจน
- ปิดได้ด้วย `Esc`
- Body Scroll ได้เมื่อข้อมูลยาว
- ใช้ Spacing / Typography / Icon Rules เดียวกับ Main UI

### Rule
Drawer ต้องรักษา Context ของหน้าหลัก เพื่อให้ผู้ใช้ปิดแล้วกลับไปเปรียบเทียบ Table เดิมได้ทันที

## 4.11 Context Deduplication

หากหน้าจอมี Context หลักที่ชัดเจนแล้ว:

- ไม่แสดงข้อมูลเดิมซ้ำในทุก Row
- เก็บเฉพาะข้อมูลที่ช่วยตัดสินใจใน Table/List
- อนุญาตให้ข้อมูล Context ซ้ำใน Floating Detail เช่น Drawer เมื่อจำเป็นต่อความเข้าใจ

**ตัวอย่าง:** หน้า Supplier มี Product Context ด้านบน จึงไม่ต้องแสดง Product Name / Brand / Packaging ซ้ำใต้ชื่อ Supplier ทุก Row

## 4.12 Supplier Purchase History

Purchase History ใช้เพื่อช่วยตีความ “ราคาล่าสุด” ไม่ให้ผู้ใช้เข้าใจว่าเป็นราคาปัจจุบันที่ Supplier ยืนยันแล้ว

### Minimum Fields
- Purchase Date
- Unit Price
- Purchase Quantity
- Purchase Condition

### Rules
- เรียงจากล่าสุดไปเก่า
- แสดงใน Drawer
- ราคาหลักใน Main Table ใช้คำว่า `ราคาล่าสุด`
- ต้องมี Price Disclaimer

## 4.13 Heuristic Rules

### R-01 — Recognition Rather Than Recall
**Dashboard — ยาใกล้หมดอายุ**

ทุกรายการยาใกล้หมดอายุต้องแสดงข้อมูลต่อไปนี้ในพื้นที่เดียวกัน:
1. รูปภาพผลิตภัณฑ์
2. Brand
3. Packaging
4. Strength
5. วันหมดอายุ

ข้อมูลทั้งหมดต้องมองเห็นได้จากรายการเดียว โดยไม่ต้องกดเข้าไปเพื่อระบุตัวยา

**Acceptance:** ทดสอบ 10 รายการ ต้องผ่าน 10/10  
**Implementation Rule:** ห้ามแสดง Expiry Item แบบ Text-only

---

### R-02 — Recognition Rather Than Recall
**Dashboard — สินค้าใกล้หมดสต็อก**

ทุกรายการต้องแสดง:
1. Thumbnail / ภาพบรรจุภัณฑ์
2. ชื่อสินค้า
3. จำนวนคงเหลือ
4. Action สำหรับตรวจสอบ Supplier

หากผู้ใช้กดดู Supplier ต้องส่ง Context ของสินค้านั้นไปหน้า Supplier Management

**Acceptance:** ทดสอบ 10 รายการ ต้องผ่าน 10/10  
**Implementation Rule:** ห้ามมี Low Stock Item แบบ Text-only

---

### R-03 — Flexibility and Efficiency of Use
**Supplier Management**

Supplier แต่ละรายต้องมีข้อมูลที่ใช้เปรียบเทียบอย่างน้อย:
1. ราคา
2. รายละเอียดการซื้อของสินค้าที่เลือก
3. เงื่อนไขการซื้อ

ข้อมูล Supplier ต้องใช้โครงสร้าง Field เดียวกัน และผู้ใช้ต้องสามารถเปรียบเทียบได้จากหน้าเดียว

**Acceptance:** ทดสอบอย่างน้อย 2 Supplier แล้วต้อง:
- เห็น Field Structure แบบเดียวกัน
- เปรียบเทียบราคา / วันที่ / จำนวน / เงื่อนไขได้ในหน้าเดียว
- ไม่ต้องจดข้อมูลภายนอก

### Supplier Interaction Rule
- หน้านี้ช่วย “เปรียบเทียบ Supplier จากประวัติการซื้อ”
- แสดงเฉพาะ Supplier ที่เคยซื้อสินค้าที่เลือก
- ราคาคือราคาจากการซื้อครั้งล่าสุด ไม่ใช่ราคาปัจจุบันที่ยืนยันแล้ว
- ต้องมีข้อมูลติดต่อหรือ Action สำหรับดูช่องทางติดต่อ
- ไม่มีการสั่งซื้อทันทีใน Version นี้
- ต้องมีข้อความแจ้งให้ติดต่อ Supplier เพื่อยืนยันราคา

---
---

------------------------------------------------------------------------------------------

### R-04 — Consistency and Standards / Visibility of System Status
**Product & Inventory Management**

ผลิตภัณฑ์/ยา ทุกรายการต้องมีข้อมูลสำคัญที่แสดงสถานะและรายละเอียดชัดเจนอย่างน้อย:
1. ข้อมูลระบุตัวตนผลิตภัณฑ์ (ภาพถ่าย + ชื่อยา + ชื่อสามัญ + หมวดหมู่)
2. ข้อมูลสถานะคลังสินค้า (จำนวนสต็อกคงเหลือ + หน่วยนับ)
3. Badge แสดงสถานะการขาย (กำลังขาย / สต็อกต่ำ / สินค้าหมด / ระงับการขาย)

ข้อมูลผลิตภัณฑ์ทุกรายการต้องใช้โครงสร้าง Field และรูปแบบ Badge เดียวกัน และมีปุ่มจัดการ (Action) ที่เข้าถึงได้ทันทีจากตาราง

**Acceptance:** ทดสอบอย่างน้อย 2 รายการผลิตภัณฑ์แล้วต้อง:
- เห็น Field Structure และ Layout ของรายการยาแบบเดียวกันทั้งหมด
- สามารถจำแนกสถานะสินค้า (กำลังขาย / สต็อกต่ำ / หมด) ได้ทันทีจากสีและข้อความของ Badge โดยไม่ต้องคลิกเข้าไปดูข้างใน
- มีปุ่ม Action (แก้ไขข้อมูล / เปลี่ยนสถานะ / ดูซัพพลายเออร์) พร้อมใช้งานในแต่ละแถวโดยไม่ต้องเปลี่ยนหน้าหลายขั้นตอน

### Product Interaction Rule
- หน้านี้ช่วย “บริหารจัดการสถานะและข้อมูลผลิตภัณฑ์ภายในร้าน”
- สถานะสต็อกต่ำ (Low Stock) และสินค้าหมด (Out of Stock) จะถูกคำนวณและอัปเดต Badge สีให้อัตโนมัติจากจำนวนสต็อกใน Database
- การกดปุ่มเปลี่ยนสถานะ (ระงับการขาย / ยกเลิกระงับ) ต้องมีระบบยืนยัน (Confirmation Dialog) ก่อนดำเนินการเสมอเพื่อป้องกันความผิดพลาด
- การแก้ไขข้อมูลยาจะพาผู้ใช้ไปยังหน้าฟอร์มแก้ไข (`edit_medicine.php`) พร้อมดึงข้อมูลเดิมมาแสดงโดยตรง
- หากค้นหาหรือกรองข้อมูลแล้วไม่พบรายการ ต้องแสดงข้อความ Empty State ภาษาไทยอย่างชัดเจน ไม่ปล่อยให้ตารางว่างเปล่า

-------------------------------------------------------------------------------------------------------------

### R-05 — Flexibility and Efficiency of Use / Recognition Rather Than Recall
**Sales History & Order Details**

ประวัติการขายทุกรายการต้องมีข้อมูลสรุป และระบบกรองข้อมูลที่ช่วยให้ผู้ใช้ตรวจสอบย้อนหลังได้อย่างรวดเร็วอย่างน้อย:
1. ข้อมูลการสั่งซื้อ (เลขที่ใบเสร็จ + วันเวลาที่ทำรายการ + ยอดรวมชำระ)
2. ตัวกรองค้นหา (กรองตามช่วงวันที่ / ค้นหาจากเลขใบเสร็จ)
3. Action ดูรายละเอียดใบเสร็จย้อนหลัง (`sales_details.php` / `receipt.php`)

รายการประวัติการขายต้องแสดงผลเรียงตามลำดับเวลาล่าสุดเสมอ และผู้ใช้สามารถเปิดดูรายการสินค้าย่อยในใบเสร็จนั้นได้จากหน้าเดียวหรือ Side Drawer

**Acceptance:** ทดสอบอย่างน้อย 2 รายการขายแล้วต้อง:
- สามารถค้นหาและกรองรายการขายตามช่วงวันที่ที่ต้องการได้ถูกต้อง        **
- เห็นเลขที่ใบเสร็จ วันเวลา และยอดชำระเงินชัดเจนในทุกแถว
- สามารถกดดูรายละเอียดใบเสร็จย้อนหลังได้ทันทีโดยไม่ต้องจำข้อมูลมาค้นหาใหม่

### Sales History Interaction Rule
- หน้านี้ช่วย “ตรวจสอบและติดตามประวัติการขายย้อนหลังของร้าน”
- ค่าเริ่มต้นแสดงรายการขายล่าสุดเรียงจากปัจจุบันย้อนหลัง
- ยอดรวมการขายจะคำนวณสรุปให้อัตโนมัติตามเงื่อนไขการกรองค้นหาที่ผู้ใช้เลือก
- หากไม่มีประวัติการขายในช่วงเวลาที่ค้นหา ต้องแสดง Empty State ว่า `ยังไม่มีรายการขายในช่วงเวลานี้`

---------------------------------------------------------------------------------------------------------
### R-04 (เพิ่มเติมหลัง HTS #3) — Add Medicine Form Feedback
ผลทดสอบ SUS/Observation: ผู้ทดสอบ A คลิกพลาดที่ช่องวันหมดอายุ, B ถามว่า "ต้องเลือกตรงนี้ใช่ไหม", E พูดว่า "ทำไมไม่ไปไหน…" หลังกดบันทึก (Task 3, FORM 5)

ฟอร์มเพิ่มยาต้อง:
1. ทำเครื่องหมาย `*` ทุกช่องที่จำเป็น และมีข้อความ `ช่องที่มี * จำเป็นต้องกรอก`
2. ช่องที่ยังไม่ครบแสดง Error State ตาม 4.2 (Border `danger-600` + ข้อความใต้ช่อง) โดยไม่ล้างค่าที่กรอกไว้
3. ช่องวันหมดอายุแสดงวันที่ที่เลือกเป็นข้อความภาษาไทยใต้ช่องทันที (ค.ศ. + พ.ศ.) และห้ามเลือกวันที่ผ่านมาแล้ว
4. ปุ่มบันทึกมี Loading State `กำลังบันทึก…` และกดซ้ำไม่ได้ระหว่างบันทึก (4.1)
5. บันทึกสำเร็จ → กลับหน้ารายการยา แสดงแถบยืนยัน `บันทึกยาใหม่แล้ว` และแถวยาที่เพิ่งเพิ่มอยู่บนสุดพร้อม Badge `เพิ่งเพิ่ม`
6. บันทึกไม่สำเร็จ → `บันทึกข้อมูลไม่สำเร็จ ลองอีกครั้ง` (5.2) ห้ามแสดงข้อความ Error ของฐานข้อมูล

**Acceptance:** เพิ่มยา 1 รายการแล้วต้อง (1) เห็น Loading State หลังกดบันทึก (2) เห็นแถบยืนยันและแถวยาใหม่บนสุดโดยไม่ต้องค้นหา (3) ส่งฟอร์มว่างแล้วเห็นข้อความใต้ทุกช่อง `*`

------------------------------------------------------------------------------------------

### R-06 — Visibility of System Status / Recognition Rather Than Recall
**Sell Medicine (ขายยา → รับชำระเงิน)** — *Pain Point ใหม่จาก HTS #3 ไม่อยู่ใน Audit HTS #1*

ผลทดสอบ: Task 1 ผู้ทดสอบ A, B, E, F หาจุดเพิ่มยาเข้าบิล/ปุ่มดำเนินการขายไม่เจอในครั้งแรก, E ต้องได้รับความช่วยเหลือ (P), B Error 1 ครั้ง, SEQ เฉลี่ย 4.60 ต่ำกว่าค่าอ้างอิง 5.5 · คำตอบปลายเปิด A: "หาปุ่มดำเนินการขายยาไม่เจอ"

หน้าขายยาต้อง:
1. แสดงแถบขั้นตอน `1 เลือกยาเข้าบิล › 2 ตรวจบิล › 3 รับชำระเงิน` และไฮไลต์ขั้นปัจจุบันทั้งหน้าขายยาและหน้ารับเงิน
2. เลือกยาจากช่องค้นหาแล้วเพิ่มเข้าบิลทันที ไม่มีปุ่มเพิ่มแยกที่ต้องกดอีกครั้ง และแสดงข้อความยืนยัน `เพิ่ม [ชื่อยา] เข้าบิลแล้ว`
3. ตัวเลือกยาแสดง ชื่อยา + ชื่อสามัญ + ราคาต่อหน่วย + คงเหลือ และค้นหาได้จากชื่อสามัญ
4. บิลว่างต้องมี Empty State `ยังไม่มียาในบิล` พร้อมบอกสิ่งที่ต้องทำต่อ
5. ปุ่มไปขั้นรับเงินปิดไว้เมื่อบิลว่าง และเมื่อมีรายการต้องบอก `จำนวนรายการ · ยอดรวม` บนปุ่ม
6. หน้ารับเงินบอกเงินที่ขาดเมื่อรับเงินไม่พอ และปุ่มยืนยันใช้คำว่า `ยืนยันรับเงินและจบการขาย`

**Acceptance:** ทำ Task 1 (Paracetamol 500mg × 2, Cetirizine 10mg × 1, เงินสด 100) แล้วต้อง (1) ไม่มีขั้นตอนกดปุ่มเพิ่มแยก (2) ปุ่มไปขั้นรับเงินแสดง `2 รายการ · ฿12.50` (3) หน้ารับเงินแสดงเงินทอน ฿87.50 และจบที่ใบเสร็จ

---------------------------------------------------------------------------------------------------------
# 5. Voice & Tone

## 5.1 Voice Principles

ข้อความของระบบต้องเป็นภาษาไทยแบบภาษามนุษย์ โดยใช้หลัก:

**สั้น / ชัด / บอกสิ่งที่ทำต่อ**

### Rules
1. หลีกเลี่ยงศัพท์เทคนิคเมื่อไม่จำเป็น
2. หลีกเลี่ยงข้อความยาวแบบภาษาราชการ
3. Error ต้องบอกว่าเกิดอะไรขึ้น
4. ถ้าผู้ใช้แก้ปัญหาได้ ต้องบอกสิ่งที่ควรทำต่อ
5. ข้อมูลเตือนต้องตรงกับระดับความรุนแรงของสถานะ
6. ไม่ใช้ภาษาตื่นตระหนกกับ Warning ที่ยังไม่ Critical
7. Loading Message ต้องสั้น
8. Empty State ต้องบอกว่าพื้นที่นั้นยังไม่มีข้อมูลอะไร
9. Action Label ต้องบอกสิ่งที่จะเกิดขึ้น เช่น `ดูซัพพลายเออร์`, `ดูรายละเอียด`, `ดูทั้งหมด 10 รายการ`

## 5.2 Message Examples

### Error
**ใช้:**  
`บันทึกข้อมูลไม่สำเร็จ ลองอีกครั้ง`

**ไม่ใช้:**  
`เกิดข้อผิดพลาดในการดำเนินการ กรุณาดำเนินการใหม่อีกครั้ง`

### Low Stock
`เหลือ 5 กล่อง ควรตรวจสอบซัพพลายเออร์`

### Near Expiry
`สินค้านี้ใกล้หมดอายุ ควรตรวจสอบสต็อก`

### Empty State
`ยังไม่มีรายการขายในช่วงเวลานี้`

### Loading
`กำลังโหลดข้อมูล…`

### Expand List
`ดูทั้งหมด 10 รายการ`

### Supplier Price Information
`ราคานี้เป็นราคาอ้างอิงจากการซื้อครั้งล่าสุด กรุณาติดต่อซัพพลายเออร์เพื่อยืนยันราคาอีกครั้ง`

### Supplier Empty State
`ยังไม่พบประวัติซัพพลายเออร์สำหรับสินค้านี้`

---

# Design Principles Summary

1. Minimal หมายถึงลดสิ่งรบกวน แต่ต้องยังมี Visual Landmark ที่เพียงพอ
2. ใช้ Typography, Whitespace, Position และ Functional Icon สร้างความสำคัญก่อนใช้สี
3. สี Primary ของระบบคือ Muted Teal
4. KPI ทั้ง 4 ตัวใช้ Surface เดียวกัน ไม่แยกสี
5. Warning / Danger / Success / Info ใช้เฉพาะ Semantic State
6. Card Radius สูงสุด `12px`
7. Button/Input Radius `8px`
8. Badge Radius `6px`
9. ใช้ 8px Grid System
10. ใช้ Border ก่อน Shadow
11. หลีกเลี่ยง Card ซ้อน Card
12. Dashboard ให้ความสำคัญกับเจ้าของร้านก่อน: KPI → Trend → Operational Alerts
13. Staff Status เป็นข้อมูลรองและต้องใช้พื้นที่น้อย
14. Dashboard Low Stock และ Expiry ต้องปฏิบัติตาม R-01 และ R-02
15. Operational Alerts Default แสดง 5 รายการ และ Expand ได้ครบ 10
16. Supplier Management ต้องช่วยเปรียบเทียบ Supplier ตาม R-03
17. Product Context เป็น Single Source of Context ของหน้า Supplier
18. หลีกเลี่ยงการแสดง Context เดิมซ้ำในทุก Table Row
19. Side Drawer ใช้กับรายละเอียดที่ไม่ควรพาผู้ใช้ออกจาก Main Comparison
20. Supplier Management ไม่มีการสั่งซื้อทันทีใน Version นี้
21. ทุกข้อความที่ผู้ใช้เห็นใช้หลัก สั้น / ชัด / บอกสิ่งที่ทำต่อ

---

## Final Status

> HTS Final (พ.ย. 2569): เพิ่ม R-04 Add Medicine Form Feedback และ R-06 Sell Medicine จากผล SUS/Observation — ดู `docs/REDESIGN_NOTES.md`


**Design System Final — Validated through 2 screens × 2 iterations**

สิ่งที่เพิ่มจาก `design_v1.md` หลังทดลองจริง:
- Functional Icon System
- Expandable Compact List
- Compact Summary
- Product Context Component Specification
- Side Drawer Pattern
- Context Deduplication Rule
- Supplier Purchase History Pattern
- Semantic Color Restriction ที่ชัดขึ้น

ดูเหตุผลและหลักฐานการเปลี่ยนแปลงเพิ่มเติมใน `DESIGN_CHANGE_LOG.md`

---

# CHANGE_LOG — HTS #2B FINAL

## 2026-09-12 — Final Dashboard Interaction Decision

ไม่มีการเปลี่ยน Core Color / Typography / Spacing / Radius Token

### Dashboard Final Baseline
- ใช้ `index_redesign_v5.php` เป็น `index.php` baseline ในชุดส่งงาน
- Topbar: หน้าหลัก, จัดการผลิตภัณฑ์, จัดการซัพพลายเออร์, จัดการพนักงาน, ประวัติการขาย, ขายยา, ออกจากระบบ
- KPI 4 ตัว: ยอดขาย, จำนวนบิล, ยอดเฉลี่ยต่อบิล, จำนวนลูกค้า
- Sales Trend เป็น **Line Chart ยอดขายรายวัน**
- Time Filter Final:
  - 7 วัน
  - 30 วัน
  - Custom Date Range “จาก–ถึง”
  - Range สูงสุด 30 วัน
- ตัด Today/Hourly mode ออกจาก Final เพราะละเอียดเกินบริบทธุรกิจร้านขนาดเล็ก
- Low Stock และ Near Expiry ยังเป็น Operational Alerts หลัก
- Low Stock ส่ง `medicine_id` ไป Supplier Management
- Recent Sales ยังคงอยู่

### Data Contract
- ไม่ลด R-01/R-03 เพื่อให้ DB ผ่าน
- Brand / Packaging / Strength และ Supplier Purchase History ยังคงเป็น DB GAP
- AI ห้ามสร้าง schema หรือ mock production data เอง
