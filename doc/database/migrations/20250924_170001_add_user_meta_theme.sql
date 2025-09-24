-- Add user theme preference column to user_meta, if it doesn't exist
ALTER TABLE user_meta
  ADD COLUMN IF NOT EXISTS theme VARCHAR(64) NULL DEFAULT NULL AFTER timezone;
