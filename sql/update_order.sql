-- SQL Script สำหรับการอัปเดตลำดับ order_no

-- วิธีที่ 1: Update แต่ละรายการทีละตัว (สำหรับใช้ใน PHP)
-- UPDATE opd_kios_dep_menu
-- SET order_no = ?
-- WHERE opd_kios_dep_menu_id = ?;

-- วิธีที่ 2: Update หลายรายการพร้อมกัน (ตัวอย่าง)
-- ใช้เมื่อต้องการ update ข้อมูลด้วยตนเอง
UPDATE opd_kios_dep_menu
SET order_no = CASE
    WHEN opd_kios_dep_menu_id = 36 THEN 1
    WHEN opd_kios_dep_menu_id = 7 THEN 2
    WHEN opd_kios_dep_menu_id = 3 THEN 3
    WHEN opd_kios_dep_menu_id = 27 THEN 4
    WHEN opd_kios_dep_menu_id = 1 THEN 5
    -- เพิ่มรายการอื่นๆ ตามลำดับใหม่
    ELSE order_no
END
WHERE computer_name = 'Rockchip rk3288'
  AND opd_kios_dep_menu_id IN (36, 7, 3, 27, 1);

-- วิธีที่ 3: Update โดยใช้ CTE (Common Table Expression)
-- สำหรับข้อมูลจำนวนมาก
WITH new_orders AS (
    SELECT * FROM (VALUES
        (36, 1),  -- (opd_kios_dep_menu_id, new_order_no)
        (7, 2),
        (3, 3),
        (27, 4),
        (1, 5)
        -- เพิ่มรายการอื่นๆ ตามต้องการ
    ) AS t(id, new_order)
)
UPDATE opd_kios_dep_menu
SET order_no = new_orders.new_order
FROM new_orders
WHERE opd_kios_dep_menu.opd_kios_dep_menu_id = new_orders.id
  AND computer_name = 'Rockchip rk3288';

-- วิธีที่ 4: Reset ลำดับให้เป็น 1, 2, 3, 4, 5... ตามลำดับปัจจุบัน
WITH ranked_data AS (
    SELECT
        opd_kios_dep_menu_id,
        ROW_NUMBER() OVER (
            PARTITION BY computer_name
            ORDER BY order_no NULLS LAST, opd_kios_dep_menu_id
        ) as new_order
    FROM opd_kios_dep_menu
    WHERE computer_name = 'Rockchip rk3288'
)
UPDATE opd_kios_dep_menu
SET order_no = ranked_data.new_order
FROM ranked_data
WHERE opd_kios_dep_menu.opd_kios_dep_menu_id = ranked_data.opd_kios_dep_menu_id;

-- SQL สำหรับตรวจสอบผลลัพธ์
SELECT
    km.opd_kios_dep_menu_id,
    km.depcode,
    km.computer_name,
    km.description,
    km.order_no
FROM
    opd_kios_dep_menu AS km
WHERE
    km.order_no IS NOT NULL
    AND km.computer_name = 'Rockchip rk3288'
ORDER BY
    km.order_no;
