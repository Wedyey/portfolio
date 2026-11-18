CREATE DATABASE IF NOT EXISTS portfolio
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE portfolio;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY admins_email_unique (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS profile (
    id TINYINT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    headline VARCHAR(200) NOT NULL DEFAULT '',
    introduction TEXT NOT NULL,
    biography TEXT NOT NULL,
    email VARCHAR(254) NOT NULL DEFAULT '',
    phone VARCHAR(50) NOT NULL DEFAULT '',
    location VARCHAR(150) NOT NULL DEFAULT '',
    profile_image VARCHAR(255) NOT NULL DEFAULT '',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT profile_singleton CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS skills (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT '',
    proficiency TINYINT UNSIGNED NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT skills_proficiency_range CHECK (proficiency IS NULL OR proficiency <= 100),
    KEY skills_sort_order_index (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS projects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    technologies TEXT NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT '',
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    project_url VARCHAR(2048) NOT NULL DEFAULT '',
    source_url VARCHAR(2048) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY projects_sort_order_index (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS education (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    institution VARCHAR(200) NOT NULL,
    qualification VARCHAR(200) NOT NULL,
    field_of_study VARCHAR(200) NOT NULL DEFAULT '',
    start_date DATE NULL,
    end_date DATE NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY education_sort_order_index (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS experience (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    organization VARCHAR(200) NOT NULL,
    role VARCHAR(200) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    description TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY experience_sort_order_index (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS achievements (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    issuer VARCHAR(200) NOT NULL DEFAULT '',
    awarded_on DATE NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) NOT NULL DEFAULT '',
    sort_order INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY achievements_sort_order_index (sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY messages_read_created_index (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO profile (
    id, name, headline, introduction, biography, email, phone, location, profile_image
) VALUES (
    1,
    'Roejie Damaso',
    'Information Technology student',
    'A student building thoughtful digital experiences.',
    'I am interested in technology, programming, creativity, and web development.',
    'roejieadamso@gmail.com',
    '',
    'Philippines',
    'img/pic.jpg'
);

INSERT IGNORE INTO skills (id, name, category, proficiency, sort_order) VALUES
    (1, 'HTML', 'Web', 80, 1),
    (2, 'CSS', 'Web', 80, 2),
    (3, 'JavaScript', 'Web', 75, 3),
    (4, 'PHP', 'Backend', 70, 4),
    (5, 'MySQL', 'Database', 70, 5);

INSERT IGNORE INTO projects (
    id, title, description, technologies, category, image_path, sort_order
) VALUES (
    1,
    'Arvins Rice & Corn Record Milling System',
    'A Java and MySQL record management system for scheduling, milling status, payments, and transactions.',
    'Java, MySQL, NetBeans',
    'java',
    'img/ricemill.png',
    1
);

INSERT INTO projects (title, description, technologies, category, image_path, sort_order)
SELECT
    'VYRO Zenith - Wireless Headphone Advertisement',
    'A fictional wireless headphone advertisement created using Adobe Animate. The project presents the product through animated scenes, text, transitions, motion effects, background music, and sound effects. It was created as a school project to practice animation and multimedia design.',
    'Adobe Animate',
    'other',
    'img/Vyro.png',
    2
WHERE NOT EXISTS (
    SELECT 1 FROM projects WHERE title = 'VYRO Zenith - Wireless Headphone Advertisement'
);

INSERT INTO projects (title, description, technologies, category, image_path, sort_order)
SELECT
    'Poly art project',
    'A low-poly digital artwork of an otter created using geometric shapes and different shades to form the animal''s appearance. This project helped me practice creativity, shape composition, and digital art techniques.',
    'Adobe Illustrator',
    'other',
    'img/otter.png',
    3
WHERE NOT EXISTS (
    SELECT 1 FROM projects WHERE title = 'Poly art project'
);

INSERT INTO projects (title, description, technologies, category, image_path, sort_order)
SELECT
    'Whack-a-Mole Game',
    'A simple browser-based Whack-a-Mole game created to practice JavaScript programming and interactive web development. The game allows the player to click the mole as it appears in different positions while keeping track of the player''s score. This project helped me practice JavaScript events, functions, DOM manipulation, and basic game logic.',
    'JavaScript, HTML, CSS',
    'web',
    'img/whack.png',
    4
WHERE NOT EXISTS (
    SELECT 1 FROM projects WHERE title = 'Whack-a-Mole Game'
);

INSERT IGNORE INTO education (
    id, institution, qualification, field_of_study, start_date, end_date, description, sort_order
) VALUES (
    1,
    'Negros Oriental State University',
    'Bachelor of Science in Information Technology',
    'Information Technology',
    '2023-08-01',
    NULL,
    'Currently studying BSIT with interests in programming, web development, and databases.',
    1
);

INSERT IGNORE INTO experience (
    id, organization, role, start_date, end_date, description, sort_order
) VALUES (
    1,
    'Student Projects',
    'Student Developer',
    '2024-01-01',
    NULL,
    'Built school and personal projects involving web development, Java, PHP, MySQL, and digital design.',
    1
);
