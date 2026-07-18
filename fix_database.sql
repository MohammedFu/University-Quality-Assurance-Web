-- Fix script for university quality assurance system
-- This script fixes issues with the learning_outcome table and related views

-- First, check if lo_description column exists in learning_outcome table
-- If not, add it
SET @column_exists = 0;
SELECT COUNT(*) INTO @column_exists 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'learning_outcome' 
AND COLUMN_NAME = 'lo_description';

SET @add_column_sql = IF(@column_exists = 0, 
                         'ALTER TABLE `learning_outcome` ADD COLUMN `lo_description` varchar(250) DEFAULT NULL',
                         'SELECT "Column already exists"');
PREPARE stmt FROM @add_column_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Clear and repopulate the learning_outcome table
TRUNCATE TABLE `learning_outcome`;

-- Migrate data from topics.lo1 and topics.lo2 to learning_outcome table
-- Only run this if the lo1 and lo2 columns still exist in the topics table
SET @columns_exist = 0;
SELECT COUNT(*) INTO @columns_exist 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'topics' 
AND (COLUMN_NAME = 'lo1' OR COLUMN_NAME = 'lo2');

SET @insert_data_sql = IF(@columns_exist > 0, 
'INSERT INTO `learning_outcome` (`topic_id`, `lo_symbol`, `lo_description`)
SELECT 
    topic_id,
    SUBSTRING_INDEX(lo1, ":", 1) AS lo_symbol,
    TRIM(SUBSTRING(lo1, INSTR(lo1, ":") + 1)) AS lo_description
FROM topics
WHERE lo1 IS NOT NULL AND lo1 != ""

UNION ALL

SELECT 
    topic_id,
    SUBSTRING_INDEX(lo2, ":", 1) AS lo_symbol,
    TRIM(SUBSTRING(lo2, INSTR(lo2, ":") + 1)) AS lo_description
FROM topics
WHERE lo2 IS NOT NULL AND lo2 != ""',
'SELECT "Columns do not exist, skipping data migration"');

PREPARE stmt FROM @insert_data_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update lo_weight calculation to use learning_outcome table
TRUNCATE TABLE `lo_weight`;

-- Insert calculated weights for learning outcomes
INSERT INTO `lo_weight` (`course_id`, `lo_symbol`, `weight`)
WITH lo_data AS (
    -- Get learning outcomes with course_id from topics
    SELECT
        t.course_id,
        lo.lo_symbol
    FROM learning_outcome lo
    JOIN topics t ON lo.topic_id = t.topic_id
    WHERE t.course_id IS NOT NULL
),
lo_counts AS (
    -- Count occurrences of each LO symbol per course
    SELECT
        course_id,
        lo_symbol,
        COUNT(*) AS occurrence_count
    FROM lo_data
    GROUP BY course_id, lo_symbol
),
course_totals AS (
    -- Calculate total occurrences of all LOs per course
    SELECT
        course_id,
        SUM(occurrence_count) AS total_occurrences
    FROM lo_counts
    GROUP BY course_id
)
-- Calculate and insert the weights
SELECT
    lc.course_id,
    lc.lo_symbol,
    ROUND(lc.occurrence_count / ct.total_occurrences, 2) AS weight
FROM lo_counts lc
JOIN course_totals ct ON lc.course_id = ct.course_id
ORDER BY lc.course_id, lc.lo_symbol;

-- Update the lo_question_mapping view to use learning_outcome table
DROP VIEW IF EXISTS `lo_question_mapping`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `lo_question_mapping` AS
SELECT 
    t.course_id,
    q.topic_id,
    q.question_id,
    GROUP_CONCAT(DISTINCT CONCAT(lo.lo_symbol, ': ', lo.lo_description) SEPARATOR '|') AS learning_outcomes,
    q.question_text
FROM questions q
JOIN topics t ON q.topic_id = t.topic_id
LEFT JOIN learning_outcome lo ON t.topic_id = lo.topic_id
GROUP BY t.course_id, q.topic_id, q.question_id, q.question_text;

-- Remove lo1 and lo2 columns from topics table if they exist
SET @drop_columns_sql = IF(@columns_exist > 0, 
                          'ALTER TABLE `topics` DROP COLUMN `lo1`, DROP COLUMN `lo2`',
                          'SELECT "Columns already removed"');
PREPARE stmt FROM @drop_columns_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check if lo1 and lo2 columns exist in questions table
SET @q_columns_exist = 0;
SELECT COUNT(*) INTO @q_columns_exist 
FROM information_schema.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'questions' 
AND (COLUMN_NAME = 'lo1' OR COLUMN_NAME = 'lo2');

-- Remove lo1 and lo2 columns from questions table if they exist
SET @drop_q_columns_sql = IF(@q_columns_exist > 0, 
                           'ALTER TABLE `questions` DROP COLUMN `lo1`, DROP COLUMN `lo2`',
                           'SELECT "Columns already removed from questions table"');
PREPARE stmt FROM @drop_q_columns_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
