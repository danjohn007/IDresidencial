-- Fix permanent passes that were incorrectly saved with max_uses > 0.
-- Permanent passes must have max_uses = 0 (unlimited).
-- This also reactivates permanent passes that became unreachable due to
-- the incorrect max_uses value being exhausted.

UPDATE `resident_access_passes`
SET `max_uses`  = 0,
    `status`    = IF(`status` = 'active', 'active', `status`)
WHERE `pass_type` = 'permanent'
  AND `max_uses` != 0;

-- Reactivate permanent passes stuck in 'active' that are unreachable because
-- uses_count reached the old (wrong) max_uses limit.
-- After the fix above max_uses = 0 so the validation query allows them again.
-- No further action needed for those rows.

-- Ensure single_use passes always have max_uses = 1.
UPDATE `resident_access_passes`
SET `max_uses` = 1
WHERE `pass_type` = 'single_use'
  AND `max_uses` != 1;
