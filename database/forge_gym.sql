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

-- Dados atualizados para corresponder aos data-id do index.html (1 a 8)
INSERT INTO equipment (name, zone, total_units, in_use, image_path) VALUES
    ('Treadmills',       'Cardio zone',   12, 4, 'images/equipment/treadMills.png'),
    ('Stationary Bikes', 'Cardio zone',    8, 3, 'images/equipment/stationaryBikes.png'),
    ('Leg Press',        'Strength zone',  2, 1, 'images/equipment/legPress.png'),
    ('Chest Press',      'Strength zone',  4, 2, 'images/equipment/chestPress.png'),
    ('Weight Bench',     'Power zone',    10, 6, 'images/equipment/weightBench.png'),
    ('Squat Rack',       'Power zone',     6, 4, 'images/equipment/squatRack.png'),
    ('Cable Machine',    'Power zone',     3, 1, 'images/equipment/cableMachine.png'),
    ('Lat Pulldown',     'Strength zone',  5, 2, 'images/equipment/latPulldown.png');