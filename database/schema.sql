-- ============================================================
-- HPV·HSV Compass — Database Schema
-- Run this once to create the database and seed starter content.
-- ============================================================

CREATE DATABASE IF NOT EXISTS hpv_hsv_compass CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hpv_hsv_compass;

-- ------------------------------------------------------------
-- USERS (also holds admin accounts via the `role` column)
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    reminders_enabled TINYINT(1) NOT NULL DEFAULT 1,
    digest_enabled TINYINT(1) NOT NULL DEFAULT 0,
    signins INT NOT NULL DEFAULT 1,
    email_verified TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(64) DEFAULT NULL,
    verification_token_expires DATETIME DEFAULT NULL,
    verification_sent_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTENT (CMS-editable lesson content — HPV & HSV lessons)
-- ------------------------------------------------------------
CREATE TABLE content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic ENUM('hpv', 'hsv') NOT NULL,
    section VARCHAR(40) NOT NULL,           -- overview, symptoms, causes, prevention, vaccination
    title VARCHAR(150) NOT NULL,
    icon VARCHAR(40) DEFAULT NULL,          -- font-awesome icon class
    body MEDIUMTEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY topic_section (topic, section)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTENT MEDIA (images/videos attached to lesson sections via the CMS)
-- ------------------------------------------------------------
CREATE TABLE content_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    media_type ENUM('image', 'video') NOT NULL,
    file_path VARCHAR(255) NOT NULL,       -- relative path under /uploads/content/
    caption VARCHAR(255) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- QUIZ QUESTIONS (per topic)
-- ------------------------------------------------------------
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic ENUM('hpv', 'hsv') NOT NULL,
    question VARCHAR(255) NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) DEFAULT NULL,
    correct_option CHAR(1) NOT NULL,        -- 'a', 'b', or 'c'
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- FAQ (public FAQ page content, admin-editable)
-- ------------------------------------------------------------
CREATE TABLE faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- LEARNING PROGRESS (one row per user per topic)
-- ------------------------------------------------------------
CREATE TABLE learning_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic ENUM('hpv', 'hsv') NOT NULL,
    overview_done TINYINT(1) NOT NULL DEFAULT 0,
    symptoms_done TINYINT(1) NOT NULL DEFAULT 0,
    causes_done TINYINT(1) NOT NULL DEFAULT 0,
    prevention_done TINYINT(1) NOT NULL DEFAULT 0,
    vaccination_done TINYINT(1) NOT NULL DEFAULT 0,
    quiz_done TINYINT(1) NOT NULL DEFAULT 0,
    quiz_score INT DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY user_topic (user_id, topic),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- SAVED ARTICLES (bookmarks)
-- ------------------------------------------------------------
CREATE TABLE saved_articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    topic ENUM('hpv', 'hsv') NOT NULL,
    section VARCHAR(40) NOT NULL,
    saved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY user_topic_section (user_id, topic, section),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BOOKINGS (screening appointments)
-- ------------------------------------------------------------
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name VARCHAR(120) NOT NULL,
    test_type VARCHAR(120) NOT NULL,
    clinic VARCHAR(150) NOT NULL,
    appointment_date DATE NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- AI ASSISTANT CHAT LOG (for progress stats + admin visibility)
-- ------------------------------------------------------------
CREATE TABLE chat_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    question VARCHAR(500) NOT NULL,
    answer TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- CONTACT MESSAGES (from the public contact page)
-- ------------------------------------------------------------
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default admin account
--   email:    admin@hpvhsvcompass.local
--   password: Admin@123
-- Change this password immediately after first login (Settings page).
INSERT INTO users (name, email, password_hash, role, email_verified) VALUES
('Site Administrator', 'admin@hpvhsvcompass.local', '$2y$10$ppzK7HOiXPTjjs2UlPlUaeJtQZOzAP8pVzZzIdyHydkg6pdASisC2', 'admin', 1);

-- ---------------- CONTENT: HPV ----------------
INSERT INTO content (topic, section, title, icon, body, sort_order) VALUES
('hpv', 'overview', 'Overview', 'fa-book',
 '<p><strong>Full name:</strong> Human Papillomavirus. A group of more than 200 related viruses, some of which are spread through sexual contact.</p>
  <div class="callout"><i class="fas fa-lightbulb"></i> <strong>Did you know?</strong> About 90% of HPV infections clear within two years without treatment.</div>', 1),

('hpv', 'symptoms', 'Symptoms', 'fa-notes-medical',
 '<ul class="info-list">
    <li>Often no symptoms at all.</li>
    <li>Genital warts (for low-risk types 6 and 11).</li>
    <li>Cellular changes that can lead to cervical, anal, or throat cancer (high-risk types 16, 18).</li>
  </ul>', 2),

('hpv', 'causes', 'Causes', 'fa-exchange-alt',
 '<p><strong>Ways of getting HPV</strong></p>
  <ul class="info-list">
    <li>Skin-to-skin contact during vaginal, anal, or oral sex.</li>
    <li>Rarely, from mother to baby during childbirth.</li>
    <li>Any intimate contact with an infected area, even without visible warts.</li>
  </ul>', 3),

('hpv', 'prevention', 'Prevention', 'fa-shield-virus',
 '<ul class="info-list">
    <li>Consistent condom use reduces transmission risk.</li>
    <li>Regular Pap smears and HPV testing for early detection.</li>
  </ul>
  <p style="margin-top:14px;"><strong>Treatment &amp; medications</strong></p>
  <ul class="info-list">
    <li>No cure for the virus itself, but the body clears most infections naturally.</li>
    <li>Treatments for genital warts: topical creams (imiquimod), cryotherapy, or surgical removal.</li>
    <li>For precancerous changes: loop electrosurgical excision procedure (LEEP) or cryotherapy.</li>
  </ul>', 4),

('hpv', 'vaccination', 'Vaccination', 'fa-syringe',
 '<ul class="info-list">
    <li>HPV vaccine (Gardasil 9) — highly effective, recommended for ages 9–45.</li>
    <li>Given as a 2 or 3-dose series depending on age at first dose.</li>
    <li>Protects against the HPV types responsible for most cervical, anal, and throat cancers.</li>
  </ul>', 5);

INSERT INTO quiz_questions (topic, question, option_a, option_b, option_c, correct_option, sort_order) VALUES
('hpv', 'What does HPV stand for?', 'Human Papillomavirus', 'Human Prion Virus', 'Herpes Papilloma Virus', 'a', 1),
('hpv', 'Which of these can help prevent HPV?', 'Antibiotics', 'The Gardasil 9 vaccine', 'Daily multivitamins', 'b', 2),
('hpv', 'True or false: most HPV infections cause no symptoms.', 'True', 'False', NULL, 'a', 3);

-- ---------------- CONTENT: HSV ----------------
INSERT INTO content (topic, section, title, icon, body, sort_order) VALUES
('hsv', 'overview', 'Overview', 'fa-book',
 '<p><strong>Full name:</strong> Herpes Simplex Virus. Two main types: HSV-1 (oral herpes) and HSV-2 (genital herpes).</p>
  <div class="callout"><i class="fas fa-people-arrows"></i> Most people with HSV live healthy, active lives. Support groups and therapy are available.</div>', 1),

('hsv', 'symptoms', 'Symptoms', 'fa-notes-medical',
 '<ul class="info-list">
    <li>Blisters, sores, or ulcers around mouth, genitals, or rectum.</li>
    <li>Itching, burning, or tingling before outbreak.</li>
    <li>Flu-like symptoms during first outbreak (fever, body aches).</li>
  </ul>', 2),

('hsv', 'causes', 'Causes', 'fa-exchange-alt',
 '<p><strong>Ways of getting HSV</strong></p>
  <ul class="info-list">
    <li>Oral-to-oral contact (HSV-1) — kissing or sharing utensils.</li>
    <li>Skin-to-skin contact during vaginal, anal, or oral sex (HSV-1 and HSV-2).</li>
    <li>Mother to newborn during childbirth (neonatal herpes, rare but serious).</li>
  </ul>', 3),

('hsv', 'prevention', 'Prevention', 'fa-shield-virus',
 '<ul class="info-list">
    <li>Condoms reduce but do not eliminate risk (lesions may be outside covered area).</li>
    <li>Open communication with partners about HSV status.</li>
    <li>Avoiding sexual contact during outbreaks.</li>
  </ul>', 4),

('hsv', 'vaccination', 'Treatment', 'fa-pills',
 '<p><strong>Treatment &amp; medications</strong></p>
  <ul class="info-list">
    <li>Antiviral drugs: Acyclovir, Valacyclovir, Famciclovir reduce outbreak severity and frequency.</li>
    <li>Daily suppressive therapy lowers transmission risk by ~50%.</li>
    <li>Topical creams may help with symptoms but are less effective than oral antivirals.</li>
  </ul>', 5);

INSERT INTO quiz_questions (topic, question, option_a, option_b, option_c, correct_option, sort_order) VALUES
('hsv', 'What does HSV stand for?', 'Herpes Simplex Virus', 'Human Skin Virus', 'Herpes Spread Vector', 'a', 1),
('hsv', 'Which medication class helps manage HSV outbreaks?', 'Antibiotics', 'Antivirals (e.g. Valacyclovir)', 'Antihistamines', 'b', 2),
('hsv', 'True or false: condoms eliminate all HSV transmission risk.', 'True', 'False', NULL, 'b', 3);

-- ---------------- FAQ ----------------
INSERT INTO faqs (question, answer, sort_order) VALUES
('Is HPV the same as HSV?', 'No. HPV (Human Papillomavirus) and HSV (Herpes Simplex Virus) are different virus families with different symptoms, risks, and management. This platform covers both because they are both common sexually transmitted infections that are frequently confused.', 1),
('Can HPV or HSV be cured?', 'There is no cure for either virus, but both are manageable. Most HPV infections clear on their own within two years. HSV outbreaks can be controlled with antiviral medication, and many people go long periods without symptoms.', 2),
('Do I need symptoms to get tested?', 'No. Many HPV and HSV infections cause no symptoms at all. Regular screening is the most reliable way to know your status, regardless of symptoms.', 3),
('Is the HPV vaccine only for teenagers?', 'The HPV vaccine (Gardasil 9) is recommended for ages 9–45. It is most effective before exposure to the virus, but can still provide protection against HPV types you have not yet encountered later in life.', 4),
('How accurate is the AI Assistant?', 'The AI Assistant provides general educational information about HPV and HSV only. It is not a substitute for professional medical advice, diagnosis, or treatment. Always consult a qualified healthcare provider about your specific situation.', 5),
('Is my information private?', 'Your account information, learning progress, and bookings are stored securely and are only accessible to you and authorized administrators for the purpose of operating this platform.', 6);
