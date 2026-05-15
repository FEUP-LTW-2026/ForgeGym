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

-- 3. Criar a tabela de Aulas (Schedule)
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

-- 4. Insert Trainer data in English
INSERT INTO trainers (id, name, role, bio, specializations, quote, image_path) VALUES
(1, 'Luis Torrao', 'Head of Strength & Conditioning', 'Strength training specialist with over 10 years of experience shaping athletes. My focus is on ensuring every movement is executed with perfect technique to maximize gains and prevent injuries.', 'Strength, Powerlifting', '"True strength doesn''t just come from the muscle, but from the unwavering discipline of showing up every day."', 'images/personal trainers/pt-torrao.jpeg'),
(2, 'Diogo Quaresma', 'HIIT & Cardio Specialist', 'Trainer focused on high-intensity workouts and improving cardiovascular capacity and endurance. Get ready to sweat and push past your mental barriers.', 'HIIT, Endurance', '"There are no shortcuts. Today''s sweat is tomorrow''s energy. Let''s push your heart to the limit!"', 'images/personal trainers/pt-quaresma.jpeg'),
(3, 'Mafalda Pacheco', 'Mobility & Yoga Master', 'Expert in flexibility, postural alignment, and active body recovery. I believe that a strong body needs to be a mobile and pain-free body.', 'Yoga, Recovery', '"Your body is your temple. My goal is to teach you how to move with freedom and without pain."', 'images/personal trainers/pt-mafalda.jpeg'),
(4, 'David Pinto', 'Pilates & Core Expert', 'Focused on core stabilization, correct posture, and deep muscle strengthening. Ideal for those looking for a solid foundation for any sport.', 'Pilates, Core', '"Everything starts at the center. A strong core is the armor that protects and empowers your entire body."', 'images/personal trainers/pt-david.jpeg'),
(5, 'Joao Lage', 'Functional & Kettlebell', 'Dynamic training focused on functional movements and high performance with kettlebells. Prepare your body for the demands of daily life with explosive power.', 'Functional, CrossFit', '"Train for life, not just for the mirror. Strength, speed, and agility all in one package."', 'images/personal trainers/pt-lage.jpeg');