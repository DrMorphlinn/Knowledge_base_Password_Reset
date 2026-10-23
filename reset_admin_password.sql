-- Reset the existing Knowledge Base Portal admin password.
-- Run this against the existing `knowledge_base` database.
-- Temporary password: KB-Admin-Reset-2026!N7vQ

USE `knowledge_base`;

UPDATE `users`
SET `password` = '$2y$12$1azCjmAbmdmfXobdfvUhVuY5uVmU.YcAvXp4.pIEx8qwyypxTz/Hm'
WHERE `email` = 'admin@knowledgebase.com'
  AND `role` = 'admin';

-- Verify that exactly one admin account matched:
SELECT `id`, `name`, `email`, `role`
FROM `users`
WHERE `email` = 'admin@knowledgebase.com'
  AND `role` = 'admin';
