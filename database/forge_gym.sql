-- Tabela principal de equipamentos
CREATE TABLE IF NOT EXISTS equipment (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT    NOT NULL,
    zone        TEXT    NOT NULL,
    total_units INTEGER NOT NULL DEFAULT 0,
    in_use      INTEGER NOT NULL DEFAULT 0,
    image_path  TEXT,
    CHECK (in_use >= 0),
    CHECK (in_use <= total_units)
);

INSERT INTO equipment (name, zone, total_units, in_use, image_path) VALUES
    ('Treadmills',       'Cardio zone',   12, 4, 'images/equipment/treadMills.png'),
    ('Stationary Bikes', 'Cardio zone',    8, 3, 'images/equipment/stationaryBikes.png'),
    ('Leg Press',        'Strength zone',  2, 1, 'images/equipment/legPress.png'),
    ('Chest Press',      'Strength zone',  4, 2, 'images/equipment/chestPress.png'),
    ('Weight Bench',     'Power zone',    10, 6, 'images/equipment/weightBench.png'),
    ('Squat Rack',       'Power zone',     6, 4, 'images/equipment/squatRack.png'),
    ('Cable Machine',    'Power zone',     3, 1, 'images/equipment/cableMachine.png'),
    ('Lat Pulldown',     'Strength zone',  5, 2, 'images/equipment/latPulldown.png');

-- 1. Apagar as tabelas antigas se existirem
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS trainers;

-- 2. Criar a tabela de Treinadores COM A NOVA COLUNA "quote"
CREATE TABLE trainers (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT NOT NULL,
    role            TEXT,
    bio             TEXT,
    specializations TEXT,
    quote           TEXT,
    image_path      TEXT
);

-- 3. Insert Trainer data in English
INSERT INTO trainers (id, name, role, bio, specializations, quote, image_path) VALUES
(1, 'Luis Torrao', 'Head of Strength & Conditioning', 'Strength training specialist with over 10 years of experience shaping athletes. My focus is on ensuring every movement is executed with perfect technique to maximize gains and prevent injuries.', 'Strength, Powerlifting', '"True strength doesn''t just come from the muscle, but from the unwavering discipline of showing up every day."', 'images/personal trainers/pt-torrao.jpeg'),
(2, 'Diogo Quaresma', 'HIIT & Cardio Specialist', 'Trainer focused on high-intensity workouts and improving cardiovascular capacity and endurance. Get ready to sweat and push past your mental barriers.', 'HIIT, Endurance', '"There are no shortcuts. Today''s sweat is tomorrow''s energy. Let''s push your heart to the limit!"', 'images/personal trainers/pt-quaresma.jpeg'),
(3, 'Mafalda Pacheco', 'Mobility & Yoga Master', 'Expert in flexibility, postural alignment, and active body recovery. I believe that a strong body needs to be a mobile and pain-free body.', 'Yoga, Recovery', '"Your body is your temple. My goal is to teach you how to move with freedom and without pain."', 'images/personal trainers/pt-mafalda.jpeg'),
(4, 'David Pinto', 'Pilates & Core Expert', 'Focused on core stabilization, correct posture, and deep muscle strengthening. Ideal for those looking for a solid foundation for any sport.', 'Pilates, Core', '"Everything starts at the center. A strong core is the armor that protects and empowers your entire body."', 'images/personal trainers/pt-david.jpeg'),
(5, 'Joao Lage', 'Functional & Kettlebell', 'Dynamic training focused on functional movements and high performance with kettlebells. Prepare your body for the demands of daily life with explosive power.', 'Functional, CrossFit', '"Train for life, not just for the mirror. Strength, speed, and agility all in one package."', 'images/personal trainers/pt-lage.jpeg');

-- 4. Criar a tabela de Aulas (Schedule)
CREATE TABLE classes (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    type        TEXT NOT NULL,
    trainer_id  INTEGER,
    day_of_week TEXT,
    start_time  TEXT,
    end_time    TEXT,
    capacity    INTEGER,
    FOREIGN KEY (trainer_id) REFERENCES trainers(id)
);

INSERT INTO classes (name, type, trainer_id, day_of_week, start_time, end_time, capacity) VALUES
-- Monday
('Powerlifting Foundation', 'Strength', 1, 'Monday', '09:00', '10:00', 15),
('Morning HIIT Blast', 'HIIT', 2, 'Monday', '10:30', '11:30', 20),
('Sunrise Yoga', 'Yoga', 3, 'Monday', '08:00', '09:00', 12),
('Functional Circle', 'Functional', 5, 'Monday', '18:00', '19:00', 15),

-- Tuesday
('Core & Stability', 'Pilates', 4, 'Tuesday', '09:00', '10:00', 10),
('Kettlebell Skills', 'Functional', 5, 'Tuesday', '17:00', '18:00', 12),
('Hypertrophy Session', 'Strength', 1, 'Tuesday', '18:30', '19:30', 15),

-- Wednesday
('Vinyasa Flow', 'Yoga', 3, 'Wednesday', '18:00', '19:00', 12),
('Endurance Cardio', 'HIIT', 2, 'Wednesday', '19:00', '20:00', 20),
('Posture Correction', 'Pilates', 4, 'Wednesday', '11:00', '12:00', 10),

-- Thursday
('Deadlift Mastery', 'Strength', 1, 'Thursday', '18:00', '19:00', 15),
('HIIT Express', 'HIIT', 2, 'Thursday', '08:00', '08:45', 20),
('Restorative Yoga', 'Yoga', 3, 'Thursday', '19:30', '20:30', 12),

-- Friday
('Fat Burn Friday', 'HIIT', 2, 'Friday', '17:30', '18:15', 20),
('CrossFit WOD', 'Functional', 5, 'Friday', '18:30', '19:30', 15),
('Advanced Pilates', 'Pilates', 4, 'Friday', '12:00', '13:00', 10),

-- Saturday
('Weekend Warrior Strong', 'Strength', 1, 'Saturday', '10:00', '11:30', 20),
('Morning Mobility', 'Yoga', 3, 'Saturday', '09:00', '10:00', 15),
('Kettlebell Bootcamp', 'Functional', 5, 'Saturday', '11:00', '12:00', 20);

-- Criar a tabela de Utilizadores
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL, -- Aqui vamos guardar a password encriptada (hash)
    profile_photo TEXT DEFAULT 'images/profiles/default.png',
    is_admin INTEGER DEFAULT 0 -- 0 para membro normal, 1 para administrador
);

-- Inserir um utilizador de teste (Password é '12345' encriptada com bcrypt)
INSERT INTO users (name, username, email, password, is_admin) 
VALUES ('John Doe', 'johndoe', 'john@forgegym.pt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);