-- 1. নতুন ইউজার সাইনআপ
INSERT INTO Users (user_id, full_name, contact_number, email_id, password, profession, created_at, status)
VALUES ('USER301', 'Karim Rahman', '01799999999', 'karim@gmail.com', '$2y$10$hashedpass', 'Doctor', NOW(), 'active');

-- 2. লগইন চেক
SELECT user_id, full_name, role, mess_id, status, photo FROM Users 
WHERE email_id = 'rakib@gmail.com' AND password = '$hashed_password' AND status = 'active';

-- 3. মেসে জয়েন করা (অ্যাডমিন অ্যাপ্রুভ করলে)
UPDATE Users SET mess_id = 'MESS001', status = 'active' WHERE user_id = 'USER301';

-- 4. আজকের সব মিল + কতজন টিক দিয়েছে (হোম স্ক্রিন)
SELECT meal_id, meal_type, menu, 
       (SELECT COUNT(*) FROM meal_attendances WHERE meal_id = Meals.meal_id AND attended = 1) as attended_count
FROM Meals WHERE mess_id = 'MESS001' AND meal_date = CURDATE()
ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'snacks', 'dinner');

-- 5. আমি আজকের কোন মিলে টিক দিয়েছি?
SELECT meal_id, attended FROM meal_attendances 
WHERE user_id = 'USER101' AND meal_id IN (SELECT meal_id FROM Meals WHERE meal_date = CURDATE() AND mess_id = 'MESS001');

-- 6. মিলে টিক দেওয়া (Attendance Mark)
INSERT INTO meal_attendances (meal_id, user_id, attended, attended_at) 
VALUES (25, 'USER101', TRUE, NOW())
ON DUPLICATE KEY UPDATE attended = TRUE, attended_at = NOW();

-- 7. মিলে টিক তুলে দেওয়া
UPDATE meal_attendances SET attended = FALSE WHERE meal_id = 25 AND user_id = 'USER101';

-- 8. এই মাসে আমার টোটাল মিল + আনুমানিক বিল
SELECT 
    COUNT(*) as total_meals,
    COUNT(*) * (SELECT meal_rate FROM meal_rates WHERE mess_id = 'MESS001' ORDER BY applicable_from DESC LIMIT 1) as estimated_bill
FROM meal_attendances ma
JOIN Meals m ON ma.meal_id = m.meal_id
WHERE ma.user_id = 'USER101' AND ma.attended = 1
  AND m.meal_date BETWEEN '2025-07-01' AND '2025-07-31';

-- 9. মাস শেষে অটো বিল জেনারেট (এডমিন রান করবে)
INSERT INTO monthly_bills (mess_id, user_id, bill_month, bill_year, total_meals, meal_rate, total_amount, due_amount)
SELECT 
    m.mess_id, ma.user_id, YEAR(CURDATE()), MONTH(CURDATE()),
    COUNT(ma.attended),
    mr.meal_rate,
    COUNT(ma.attended) * mr.meal_rate,
    COUNT(ma.attended) * mr.meal_rate
FROM meal_attendances ma
JOIN Meals m ON ma.meal_id = m.meal_id
JOIN meal_rates mr ON m.mess_id = mr.mess_id
WHERE m.mess_id = 'MESS001' 
  AND m.meal_date BETWEEN '2025-07-01' AND '2025-07-31'
  AND mr.applicable_from = (SELECT MAX(applicable_from) FROM meal_rates WHERE mess_id = 'MESS001')
GROUP BY ma.user_id;

-- 10. আমার চলতি মাসের বিল
SELECT total_meals, meal_rate, total_amount, paid_amount, due_amount, status 
FROM monthly_bills WHERE user_id = 'USER101' AND bill_month = YEAR(CURDATE()) AND bill_year = MONTH(CURDATE());

-- 11. পেমেন্ট রেকর্ড করা
INSERT INTO payments (mess_id, user_id, amount, payment_for, payment_month, payment_year, payment_method, transaction_id)
VALUES ('MESS001', 'USER101', 6000.00, 'both', YEAR(CURDATE()), MONTH(CURDATE()), 'bkash', 'BK987654321');

-- 12. এই মাসে কে কত পেমেন্ট করেছে
SELECT u.full_name, p.amount, p.payment_method, p.paid_at FROM payments p
JOIN Users u ON p.user_id = u.user_id
WHERE p.mess_id = 'MESS001' AND p.payment_year = 2025 AND p.payment_month = 7
ORDER BY p.paid_at DESC;

-- 13. মেসের এই মাসের টোটাল প্রফিট
SELECT 
    COALESCE(SUM(p.amount), 0) as total_income,
    COALESCE(SUM(e.amount), 0) as total_expense,
    COALESCE(SUM(p.amount), 0) - COALESCE(SUM(e.amount), 0) as profit
FROM Mess m
LEFT JOIN payments p ON m.mess_id = p.mess_id AND p.payment_year = 2025 AND p.payment_month = 7
LEFT JOIN expenses e ON m.mess_id = e.mess_id AND YEAR(e.expense_date) = 2025 AND MONTH(e.expense_date) = 7
WHERE m.mess_id = 'MESS001';

-- 14. মেসে কতগুলো সিট খালি আছে?
SELECT (capacity - COUNT(u.user_id)) as vacant_seats FROM Mess m
LEFT JOIN Users u ON m.mess_id = u.mess_id AND u.status = 'active'
WHERE m.mess_id = 'MESS001';

-- 15. সব একটিভ সিট অ্যাড দেখা
SELECT sa.*, m.mess_name, m.address FROM seat_ads sa
JOIN Mess m ON sa.mess_id = m.mess_id
WHERE sa.is_active = 1 AND sa.expires_at >= CURDATE();

-- 16. নতুন অ্যানাউন্সমেন্ট পোস্ট
INSERT INTO announcements (mess_id, title, message, posted_by) VALUES ('MESS001', 'কাল ছুটি', 'কাল মেসে কেউ থাকবে না। সবাই বাড়ি যান।', 'USER001');

-- 17. লাস্ট ১০টা অ্যানাউন্সমেন্ট
SELECT a.title, a.message, u.full_name, a.created_at FROM announcements a
JOIN Users u ON a.posted_by = u.user_id
WHERE a.mess_id = 'MESS001' ORDER BY a.created_at DESC LIMIT 10;

-- 18. আজকের বাজার লিস্ট
SELECT items, total_amount, bazaar_by FROM daily_bazar 
WHERE mess_id = 'MESS001' AND bazar_date = CURDATE();

-- 19. এই মাসের টোটাল বাজার খরচ
SELECT SUM(total_amount) as monthly_bazar FROM daily_bazar 
WHERE mess_id = 'MESS001' AND YEAR(bazar_date) = 2025 AND MONTH(bazar_date) = 7;

-- 20. আমি কোন রুমে থাকি?
SELECT r.room_number, r.facilities FROM room_members rm
JOIN rooms r ON rm.room_id = r.room_id
WHERE rm.user_id = 'USER101' AND rm.is_current = 1;

-- 21. মেসের সব মেম্বার লিস্ট
SELECT user_id, full_name, contact_number, profession, joined_date FROM Users 
WHERE mess_id = 'MESS001' AND status = 'active';

-- 22. কার বিল বাকি আছে?
SELECT u.full_name, mb.due_amount FROM monthly_bills mb
JOIN Users u ON mb.user_id = u.user_id
WHERE mb.mess_id = 'MESS001' AND mb.due_amount > 0 AND mb.bill_year = 2025 AND mb.bill_month = 7;

-- 23. টপ ৫ মিল খাওয়া মেম্বার (এই মাসে)
SELECT u.full_name, COUNT(ma.attended) as meals 
FROM meal_attendances ma
JOIN Users u ON ma.user_id = u.user_id
JOIN Meals m ON ma.meal_id = m.meal_id
WHERE m.mess_id = 'MESS001' AND ma.attended = 1 AND m.meal_date LIKE '2025-07%'
GROUP BY u.user_id ORDER BY meals DESC LIMIT 5;

-- 24. লাস্ট ৭ দিনের অ্যাটেন্ডেন্স (গ্রাফের জন্য)
SELECT meal_date, meal_type, COUNT(*) as total_attended FROM Meals m
LEFT JOIN meal_attendances ma ON m.meal_id = ma.meal_id AND ma.attended = 1
WHERE m.mess_id = 'MESS001' AND m.meal_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY m.meal_date, m.meal_type;

-- 25. কারেন্ট মিল রেট কত?
SELECT meal_rate FROM meal_rates WHERE mess_id = 'MESS001' ORDER BY applicable_from DESC LIMIT 1;

-- 26. মেসের টোটাল মেম্বার + অ্যাকটিভ + অন লিভ
SELECT 
    COUNT(*) as total,
    SUM(status = 'active') as active,
    SUM(status = 'on_leave') as on_leave
FROM Users WHERE mess_id = 'MESS001';

-- 27. রুম ওয়াইজ মেম্বার লিস্ট
SELECT r.room_number, COUNT(rm.user_id) as members, r.capacity FROM rooms r
LEFT JOIN room_members rm ON r.room_id = rm.room_id AND rm.is_current = 1
WHERE r.mess_id = 'MESS001' GROUP BY r.room_id;

-- 28. নতুন মিল যোগ করা (এডমিন)
INSERT INTO Meals (mess_id, meal_date, meal_type, menu) VALUES ('MESS001', '2025-07-25', 'dinner', 'Mutton Biryani + Borhani');

-- 29. মিল এডিট করা
UPDATE Meals SET menu = 'Chicken Biryani + Firni' WHERE meal_id = 25;

-- 30. মিল ডিলিট করা
DELETE FROM Meals WHERE meal_id = 25;

-- 31. সিট অ্যাড পোস্ট করা
INSERT INTO seat_ads (mess_id, vacant_seats, rent_per_seat, contact_person, contact_number, ad_description)
VALUES ('MESS001', 2, 3800, 'Raju Bhai', '01711111111', 'AC room with balcony');

-- 32. আমার পেমেন্ট হিস্ট্রি
SELECT amount, payment_for, payment_method, paid_at FROM payments 
WHERE user_id = 'USER101' ORDER BY paid_at DESC;

-- 33. এই মাসে কত খরচ হয়েছে (ক্যাটাগরি ওয়াইজ)
SELECT category, SUM(amount) as total FROM expenses 
WHERE mess_id = 'MESS001' AND YEAR(expense_date) = 2025 AND MONTH(expense_date) = 7
GROUP BY category;

-- 34. মেসের অ্যাডমিন কে?
SELECT full_name, contact_number FROM Users WHERE user_id = (SELECT admin_id FROM Mess WHERE mess_id = 'MESS001');

-- 35. সব মেসের লিস্ট (পাবলিক পেজে দেখাবে)
SELECT mess_id, mess_name, address, capacity, (capacity - (SELECT COUNT(*) FROM Users WHERE mess_id = Mess.mess_id AND status = 'active')) as vacant
FROM Mess WHERE capacity > (SELECT COUNT(*) FROM Users WHERE mess_id = Mess.mess_id AND status = 'active');

-- 36. ইউজার প্রোফাইল আপডেট
UPDATE Users SET full_name = 'Rakibul Islam Khan', profession = 'Senior Developer' WHERE user_id = 'USER101';

-- 37. লিভ নেওয়া
UPDATE Users SET status = 'on_leave' WHERE user_id = 'USER101';

-- 38. লিভ থেকে ফিরে আসা
UPDATE Users SET status = 'active' WHERE user_id = 'USER101';

-- 39. মেস থেকে বের হয়ে যাওয়া
UPDATE Users SET mess_id = NULL, status = 'inactive' WHERE user_id = 'USER101';

-- 40. মিল রেট চেঞ্জ করা
INSERT INTO meal_rates (mess_id, meal_rate, applicable_from) VALUES ('MESS001', 98.00, '2025-08-01');

-- 41. দৈনিক খরচ যোগ করা
INSERT INTO expenses (mess_id, expense_date, category, description, amount, added_by)
VALUES ('MESS001', '2025-07-20', 'Electricity', 'July Bill', 7200.00, 'USER001');

-- 42. বাজার লিস্ট যোগ করা
INSERT INTO daily_bazar (mess_id, bazar_date, items, total_amount, bazaar_by)
VALUES ('MESS001', '2025-07-20', 'Beef 10kg, Rice 50kg, Oil 10L', 24500.00, 'Tanvir');

-- 43. রুমে মেম্বার যোগ করা
INSERT INTO room_members (room_id, user_id, joined_date) VALUES (1, 'USER301', '2025-07-20');

-- 44. রুম থেকে মেম্বার বের করা
UPDATE room_members SET is_current = 0 WHERE user_id = 'USER101' AND room_id = 1;

-- 45. মেসের টোটাল ইনকাম এভার
SELECT SUM(amount) as lifetime_income FROM payments WHERE mess_id = 'MESS001';

-- 46. মেসের টোটাল খরচ এভার
SELECT SUM(amount) as lifetime_expense FROM expenses WHERE mess_id = 'MESS001';

-- 47. সবচেয়ে বেশি পেমেন্ট করা মেম্বার
SELECT u.full_name, SUM(p.amount) as total_paid FROM payments p
JOIN Users u ON p.user_id = u.user_id
WHERE p.mess_id = 'MESS001' GROUP BY p.user_id ORDER BY total_paid DESC LIMIT 1;

-- 48. এই মাসে কোন দিন সবচেয়ে বেশি মিল খাওয়া হয়েছে
SELECT m.meal_date, COUNT(ma.attended) as total_meals FROM Meals m
LEFT JOIN meal_attendances ma ON m.meal_id = ma.meal_id AND ma.attended = 1
WHERE m.mess_id = 'MESS001' AND m.meal_date LIKE '2025-07%' GROUP BY m.meal_date ORDER BY total_meals DESC LIMIT 1;

-- 49. ইউজার সার্চ (অ্যাডমিন প্যানেল)
SELECT user_id, full_name, contact_number, email_id, profession FROM Users 
WHERE full_name LIKE '%rakib%' OR contact_number LIKE '%1111%';

-- 50. ফুল ড্যাশবোর্ড সামারি (এডমিন হোমে দেখাবে)
SELECT 
    (SELECT COUNT(*) FROM Users WHERE mess_id = 'MESS001' AND status = 'active') as total_members,
    (SELECT COUNT(*) FROM Meals WHERE mess_id = 'MESS001' AND meal_date = CURDATE()) as today_meals,
    (SELECT SUM(attended) FROM meal_attendances ma JOIN Meals m ON ma.meal_id = m.meal_id WHERE m.mess_id = 'MESS001' AND m.meal_date = CURDATE()) as today_attendance,
    (SELECT SUM(due_amount) FROM monthly_bills WHERE mess_id = 'MESS001' AND bill_year = 2025 AND bill_month = 7) as total_due,
    (SELECT SUM(amount) FROM payments WHERE mess_id = 'MESS001' AND payment_year = 2025 AND payment_month = 7) as this_month_collection;