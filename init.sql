CREATE TABLE IF NOT EXISTS rooms (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  room_number TEXT NOT NULL,
  type TEXT NOT NULL,
  status TEXT DEFAULT 'available'
);

INSERT INTO rooms (room_number, type, status) VALUES
('A101','A','available'),
('A102','A','occupied'),
('B101','B','available');
