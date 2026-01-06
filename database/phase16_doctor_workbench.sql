-- Phase 16: Doctor Workbench Schema Changes

-- Add columns for diagnosis and notes in waiting_list table
ALTER TABLE `waiting_list` 
ADD COLUMN `diagnosis` TEXT NULL DEFAULT NULL AFTER `visit_type`,
ADD COLUMN `doctor_notes` TEXT NULL DEFAULT NULL AFTER `diagnosis`;
