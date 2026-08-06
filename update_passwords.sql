-- SQL script to update user passwords using PHP bcrypt ($2y$) hashes
-- Target accounts: Institute Admins (CUK, Kannur, MGU, OU, SVU, YVU, UOH Admin) and Hub Super Admin

UPDATE `users` SET `username` = 'Idsathyan@cuk.ac.in', `password` = '$2y$10$DNPJpCLLjlLs6HQ8vKZi7eUtMyhhF45pQop2KP7ZBkK7KOV8w6x2m', `role` = 'admin' WHERE `institute_prefix` = 'cuk';
UPDATE `users` SET `username` = 'anupkesavan@kannuriuniv.ac.in', `password` = '$2y$10$kuI3GAnV1GBFPWx4hUm4vOTsxfLrSn9HR9zMjm4i.mxaD/PbZka4S', `role` = 'admin' WHERE `institute_prefix` = 'kannur';
UPDATE `users` SET `username` = 'radhakrishnanek@mgu.ac.in', `password` = '$2y$10$hqvpHlGx9o8YLd9OQdurqOjLICwLHeKzz7L.pGyNm7UneH3DsUWSe', `role` = 'admin' WHERE `institute_prefix` = 'mgu';
UPDATE `users` SET `username` = 'vijjulatha@osmania.ac.in', `password` = '$2y$10$I9/19JJzDD.iPqH8HEPza.p32LQKke13kPrBF5BSNgebNqP5wMUlS', `role` = 'admin' WHERE `institute_prefix` = 'ou';
UPDATE `users` SET `username` = 'balaji.meriga@gmail.com', `password` = '$2y$10$OJExnJmwt3QeL6Hn5ilCY.R4jEZT7oZSXEVRQPP8.U4X0XdLljYJW', `role` = 'admin' WHERE `institute_prefix` = 'svu';
UPDATE `users` SET `username` = 'sarma7@yogivemanauniversity.ac.in', `password` = '$2y$10$Qzia0YHgEYXzLzkwjrFwv.H.X0lLKWD9O7l7Wl2Epqt/BgOe3Cdam', `role` = 'admin' WHERE `institute_prefix` = 'yvu';
UPDATE `users` SET `username` = 'admin@uoh.ac.in', `password` = '$2y$10$uAVLx4PgW.5RZcmF0JhNFOnb00aBO2dnDtDVg1YPR6uEmkqJroQIa', `role` = 'admin' WHERE `institute_prefix` = 'uoh' AND `username` = 'admin@uoh.ac.in';
UPDATE `users` SET `username` = 'superadmin@uoh.ac.in', `password` = '$2y$10$S9JHjM62u1cOsBZPNXicSO3cfBBx8f9srFH/vpddbAByzA6UnUIsu', `role` = 'super_admin' WHERE `username` = 'superadmin@uoh.ac.in';
