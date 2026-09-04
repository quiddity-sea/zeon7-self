-- Create daily_context table
CREATE TABLE IF NOT EXISTS daily_context (
    id INT AUTO_INCREMENT PRIMARY KEY,
    day_name VARCHAR(20) NOT NULL UNIQUE, -- Monday, Tuesday, etc.
    theme VARCHAR(255) NOT NULL,
    tone VARCHAR(255) NOT NULL,
    tagline VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed Data from "From The Noise – Channel Design & Maintenance.md"
INSERT INTO daily_context (day_name, theme, tone, tagline) VALUES
('Monday', 'The Signal Still Comes Through', 'Sincere, emotionally resilient, grounded', 'The connection’s weak, but it’s still there'),
('Tuesday', 'Through the Static', 'Investigative, media-savvy, cutting through chaos', 'Clarity in the noise'),
('Wednesday', 'Out From the Noise', 'Calm, personal, reflective', 'A moment to breathe'),
('Thursday', '404: Hope Not Found', 'Dark realism, dry humour, failed systems', 'The system worked as intended. That’s the problem.'),
('Friday', 'The Maddest Stuff They Did This Week', 'Fast, satirical, absurd', 'Can you believe they’re getting away with this?'),
('Saturday', 'Everything’s Fine', 'Ironic normalcy, performative denial', 'Smiling while the room burns'),
('Sunday', 'The Last Warm Place', 'Soulful, warm, inward, gently defiant', 'Where the fire hasn’t gone out')
ON DUPLICATE KEY UPDATE
    theme = VALUES(theme),
    tone = VALUES(tone),
    tagline = VALUES(tagline);
