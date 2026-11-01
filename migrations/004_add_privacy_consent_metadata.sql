-- Store the policy version and time accepted by self-registered accounts.
ALTER TABLE users
    ADD COLUMN privacy_consented_at DATETIME NULL,
    ADD COLUMN privacy_policy_version VARCHAR(20) NULL;
