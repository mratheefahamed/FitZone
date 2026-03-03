-- Add new gender column
ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') AFTER password;

-- Drop age column
ALTER TABLE users DROP COLUMN age;
