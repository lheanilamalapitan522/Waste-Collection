-- Apply to databases created before the resident role was introduced.
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'collector', 'developer', 'resident') NOT NULL;
