-- The Eye: Geospatial Intelligence Viewer
-- Database schema for zeon7_self_dev

-- 1. Session tracking (all users)
CREATE TABLE IF NOT EXISTS eye_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_token   VARCHAR(64)  NOT NULL UNIQUE,
    user_id         INT UNSIGNED NULL,
    ip_hash         VARCHAR(64)  NOT NULL,
    user_agent_hash VARCHAR(64)  NOT NULL,
    opened_at       DATETIME     DEFAULT CURRENT_TIMESTAMP,
    last_seen_at    DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    layers_used     JSON         NULL,
    request_count   INT UNSIGNED DEFAULT 0,
    INDEX idx_eye_sess_user    (user_id),
    INDEX idx_eye_sess_opened  (opened_at),
    INDEX idx_eye_sess_token   (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Annotations (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_annotations (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NULL,
    label           VARCHAR(255) NULL,
    type            ENUM('polygon','route','pin') NOT NULL,
    geometry        JSON         NOT NULL,
    style           JSON         NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_eye_ann_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Saved views / bookmarks (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_saved_views (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NULL,
    label           VARCHAR(255) NOT NULL,
    camera          JSON         NOT NULL,
    layers          JSON         NOT NULL,
    sensor_style    VARCHAR(32)  NULL,
    view_type       ENUM('bookmark','tour') DEFAULT 'bookmark',
    tour_data       JSON         NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eye_view_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Agent query log (authenticated users only)
CREATE TABLE IF NOT EXISTS eye_agent_queries (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    agent_id        VARCHAR(64)  NOT NULL DEFAULT 'otec',
    session_token   VARCHAR(64)  NOT NULL,
    user_message    TEXT         NOT NULL,
    agent_reply     TEXT         NOT NULL,
    eye_command     JSON         NULL,
    executed        TINYINT(1)   DEFAULT 0,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_eye_aq_user    (user_id),
    INDEX idx_eye_aq_session (session_token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
