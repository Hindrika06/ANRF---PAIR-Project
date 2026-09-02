-- ANRF-PAIR Production Login Credentials Update Migration
-- Generated on: 2026-09-02 09:35:53 IST
-- Hashing algorithm: PASSWORD_BCRYPT (cost 10)
-- Production-safe & idempotent: updates only password field for specific existing usernames.

UPDATE users SET password = '$2y$10$4kN4YjDEMglouJSCXjyD9uPkMQOajYuBgbJ3XesvpU9mLwV4ATFK2' WHERE LOWER(username) = LOWER('Idsathyan@cuk.ac.in');
UPDATE users SET password = '$2y$10$iS7DFGFwPyecbbyw9.QMUu47uS2pIg57BMLe0vSfIwqCgPr/NkcAS' WHERE LOWER(username) = LOWER('anupkesavan@kannuriuniv.ac.in');
UPDATE users SET password = '$2y$10$C7SIZk0Uev/cMgJH.sBNlef28LuuPxasktG5zgMmkuSxODQalLXCC' WHERE LOWER(username) = LOWER('radhakrishnanek@mgu.ac.in');
UPDATE users SET password = '$2y$10$rfCLH8Bhq1rlm4zuvlgaqeAabnEq.XfN9y.PZ6OuTRR1r4LO4nv8i' WHERE LOWER(username) = LOWER('vijjulatha@osmania.ac.in');
UPDATE users SET password = '$2y$10$m8cz3Z691tLmUuPDM41MTeLsc/dS6d0qpx1MuWEVIHM6lmREQ2jX.' WHERE LOWER(username) = LOWER('balaji.meriga@gmail.com');
UPDATE users SET password = '$2y$10$U5kKAoBFYEadyHpncEZWwuNoUQTlv6qZ7b1hXIxJLOQo2EQL8vLeW' WHERE LOWER(username) = LOWER('sarma7@yogivemanauniversity.ac.in');
UPDATE users SET password = '$2y$10$/FirTED/adFgvwvrhl9MBuv8iL1Suy04pmEj7Y9RkgZJ.YIg8UArK' WHERE LOWER(username) = LOWER('admin@uoh.ac.in');
UPDATE users SET password = '$2y$10$HIW1K8vEcdeD9/.O9LEApOGf.GdKtqy/TZrXF.eMH9zGPqKwlHsBC' WHERE LOWER(username) = LOWER('superadmin@uoh.ac.in');
