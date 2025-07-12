# KIOSK MENU - Drag & Drop Order Management

## คุณสมบัติ
- แสดงรายการเมนู KIOSK และ MINI KIOSK พร้อมภาพ
- ลากและวางเพื่อจัดเรียงลำดับใหม่
- ซ่อน/แสดงข้อมูลเพื่อดูภาพชัดเจน
- บันทึกลำดับใหม่ลงฐานข้อมูล
- แสดงสีแดงเมื่อลำดับเปลี่ยนจากเดิม

## โครงสร้างไฟล์
```
kiosk-menu/
├── index.html              # หน้าหลัก
├── api/
│   ├── index.php           # API สำหรับดึงข้อมูล
│   └── update-order.php    # API สำหรับอัปเดตลำดับ
├── sample/
│   ├── 0-kiosk.json        # ตัวอย่างข้อมูล KIOSK
│   └── 1-mini-kiosk.json   # ตัวอย่างข้อมูล MINI KIOSK
└── sql/
    └── update_order.sql    # SQL scripts สำหรับอัปเดต
```

## การติดตั้ง

### 1. ตั้งค่าฐานข้อมูล
แก้ไขไฟล์ `api/update-order.php` ในส่วนการเชื่อมต่อฐานข้อมูล:

```php
// เอา comment ออกและใส่ข้อมูลที่ถูกต้อง
$pdo = new PDO("pgsql:host=localhost;dbname=your_db", $username, $password);
```

### 2. สิทธิ์ไฟล์
ให้สิทธิ์เขียนไฟล์ log:
```bash
chmod 755 api/
chmod 666 api/order_updates.log
```

## การใช้งาน

### 1. เปิดหน้าเว็บ
เข้าใช้งานที่ `http://localhost/kiosk-menu/`

### 2. เลือกประเภทเมนู
- คลิก **KIOSK** - แสดง 3 คอลัมน์
- คลิก **MINI KIOSK** - แสดง 2 คอลัมน์

### 3. จัดเรียงลำดับ
- ลากและวาง card เพื่อเปลี่ยนลำดับ
- ตัวเลขลำดับจะเปลี่ยนทันที
- สีแดง = ลำดับเปลี่ยนจากเดิม
- สีน้ำเงิน = ลำดับเดิม

### 4. ซ่อน/แสดงข้อมูล
- คลิก **ซ่อนข้อมูล** เพื่อดูภาพชัดเจน
- คลิก **แสดงข้อมูล** เพื่อแสดงข้อมูลกลับมา

### 5. บันทึกลำดับ
- คลิก **บันทึกลำดับ** เพื่อส่งข้อมูลไปยังฐานข้อมูล
- ระบบจะส่งเฉพาะรายการที่เปลี่ยนแปลง
- แสดงสถานะ: กำลังบันทึก → บันทึกแล้ว/เกิดข้อผิดพลาด

## API Endpoints

### GET `/api/kiosk/{type}`
ดึงข้อมูลเมนู
- `type`: 0 = KIOSK, 1 = MINI KIOSK

**Response:**
```json
[
  {
    "opd_kios_dep_menu_id": 36,
    "depcode": "357",
    "computer_name": "Rockchip rk3288",
    "description": "01 อาชีวเวชกรรม",
    "order_no": 1,
    "button_image": "base64_string"
  }
]
```

### POST `/api/update-order`
อัปเดตลำดับ

**Request:**
```json
{
  "updates": [
    {
      "opd_kios_dep_menu_id": 36,
      "order_no": 2
    }
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "updated": 1,
  "message": "Order updated successfully"
}
```

## SQL Commands

### อัปเดตลำดับแต่ละรายการ
```sql
UPDATE opd_kios_dep_menu
SET order_no = ?
WHERE opd_kios_dep_menu_id = ?;
```

### อัปเดตหลายรายการพร้อมกัน
```sql
UPDATE opd_kios_dep_menu
SET order_no = CASE
    WHEN opd_kios_dep_menu_id = 36 THEN 1
    WHEN opd_kios_dep_menu_id = 7 THEN 2
    -- เพิ่มรายการอื่นๆ
    ELSE order_no
END
WHERE computer_name = 'Rockchip rk3288'
  AND opd_kios_dep_menu_id IN (36, 7);
```

## การแก้ปัญหา

### ปุ่มซ่อนข้อมูลไม่ทำงาน
1. เปิด Developer Tools (F12)
2. ดูใน Console tab หาข้อผิดพลาด
3. ตรวจสอบว่า Bootstrap Icons โหลดสำเร็จ

### การบันทึกไม่สำเร็จ
1. ตรวจสอบการเชื่อมต่อฐานข้อมูลใน `api/update-order.php`
2. ดู log ใน `api/order_updates.log`
3. ตรวจสอบสิทธิ์ไฟล์

### Drag & Drop ไม่ทำงาน
1. ตรวจสอบ JavaScript errors ใน Console
2. ลองรีเฟรชหน้าเว็บ
3. ตรวจสอบว่าข้อมูล `data-id` ถูกต้อง

## Browser Support
- Chrome 88+
- Firefox 85+
- Safari 14+
- Edge 88+

## Dependencies
- Bootstrap 5.3.3
- Bootstrap Icons 1.11.0
