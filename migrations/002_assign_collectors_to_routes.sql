-- Link collector login accounts and assigned routes to driver records.
ALTER TABLE drivers
    ADD COLUMN user_id INT NULL UNIQUE,
    ADD COLUMN route_id INT NULL,
    ADD CONSTRAINT fk_drivers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_drivers_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL;
