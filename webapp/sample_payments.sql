INSERT INTO payments (student_id, payer_id, class_id, amount, final_amount, payment_date, payment_method, status, created_at) 
VALUES 
(2, 2, 1, 500000, 500000, '2024-12-01', 'bank_transfer', 'pending', NOW()),
(3, 3, 2, 750000, 750000, '2024-12-02', 'bank_transfer', 'pending', NOW()),
(4, 4, 1, 600000, 600000, '2024-12-03', 'bank_transfer', 'pending', NOW());
