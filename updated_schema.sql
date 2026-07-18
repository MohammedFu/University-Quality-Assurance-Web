-- Updated schema for university quality assurance system
-- This script removes lo1 and lo2 columns from topics table and uses learning_outcome table instead

-- Update learning_outcome table to include description
ALTER TABLE `learning_outcome`
ADD COLUMN `lo_description` varchar(250) DEFAULT NULL;

-- Clear existing data in learning_outcome table
TRUNCATE TABLE `learning_outcome`;

-- Migrate data from topics.lo1 and topics.lo2 to learning_outcome table
INSERT INTO `learning_outcome` (`topic_id`, `lo_symbol`, `lo_description`)
SELECT
    topic_id,
    SUBSTRING_INDEX(lo1, ':', 1) AS lo_symbol,
    TRIM(SUBSTRING(lo1, INSTR(lo1, ':') + 1)) AS lo_description
FROM topics
WHERE lo1 IS NOT NULL AND lo1 != ''

UNION ALL

SELECT
    topic_id,
    SUBSTRING_INDEX(lo2, ':', 1) AS lo_symbol,
    TRIM(SUBSTRING(lo2, INSTR(lo2, ':') + 1)) AS lo_description
FROM topics
WHERE lo2 IS NOT NULL AND lo2 != '';

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

-- Remove lo1 and lo2 columns from topics table
ALTER TABLE `topics` DROP COLUMN `lo1`, DROP COLUMN `lo2`;

-- Remove lo1 and lo2 columns from questions table
ALTER TABLE `questions` DROP COLUMN `lo1`, DROP COLUMN `lo2`;
