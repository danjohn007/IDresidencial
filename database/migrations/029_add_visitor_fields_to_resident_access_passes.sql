-- Add visitor information fields to resident_access_passes
-- These fields allow residents to specify visitor details when generating an access pass.
ALTER TABLE `resident_access_passes`
    ADD COLUMN `visitor_name`  VARCHAR(255) DEFAULT NULL AFTER `notes`,
    ADD COLUMN `visitor_id`    VARCHAR(100) DEFAULT NULL AFTER `visitor_name`,
    ADD COLUMN `visitor_phone` VARCHAR(20)  DEFAULT NULL AFTER `visitor_id`,
    ADD COLUMN `vehicle_plate` VARCHAR(20)  DEFAULT NULL AFTER `visitor_phone`,
    ADD COLUMN `visit_type`    VARCHAR(50)  DEFAULT NULL AFTER `vehicle_plate`;
