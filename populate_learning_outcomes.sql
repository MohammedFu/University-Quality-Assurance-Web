-- Populate learning_outcome table with sample data
-- First, check if the table is empty
SET @count = 0;
SELECT COUNT(*) INTO @count FROM learning_outcome;

-- Only populate if empty
SET @populate_sql = IF(@count = 0, 
'INSERT INTO learning_outcome (topic_id, lo_symbol, lo_description)
SELECT 
    topic_id,
    CONCAT("a", topic_id % 10 + 1),
    CASE (topic_id % 10)
        WHEN 0 THEN "Apply math, science, and engineering principles"
        WHEN 1 THEN "Design/conduct experiments and analyze data"
        WHEN 2 THEN "Design systems under constraints"
        WHEN 3 THEN "Function on multidisciplinary teams"
        WHEN 4 THEN "Identify/formulate/solve engineering problems"
        WHEN 5 THEN "Understand ethical responsibility"
        WHEN 6 THEN "Communicate effectively"
        WHEN 7 THEN "Understand global/societal impacts"
        WHEN 8 THEN "Engage in lifelong learning"
        WHEN 9 THEN "Use modern engineering tools"
    END
FROM topics
WHERE topic_id NOT IN (SELECT topic_id FROM learning_outcome)',
'SELECT "Table already has data"');

PREPARE stmt FROM @populate_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add a second learning outcome for each topic to ensure we have enough data
SET @populate_sql2 = IF(@count = 0, 
'INSERT INTO learning_outcome (topic_id, lo_symbol, lo_description)
SELECT 
    topic_id,
    CONCAT("b", topic_id % 5 + 1),
    CASE (topic_id % 5)
        WHEN 0 THEN "Analyze complex engineering problems"
        WHEN 1 THEN "Apply engineering design principles"
        WHEN 2 THEN "Develop sustainable solutions"
        WHEN 3 THEN "Evaluate engineering systems"
        WHEN 4 THEN "Integrate modern technologies"
    END
FROM topics
WHERE topic_id NOT IN (
    SELECT topic_id FROM learning_outcome 
    GROUP BY topic_id HAVING COUNT(*) > 1
)',
'SELECT "Table already has data"');

PREPARE stmt FROM @populate_sql2;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Recalculate weights
TRUNCATE TABLE lo_weight;

INSERT INTO lo_weight (course_id, lo_symbol, weight)
WITH lo_data AS (
    SELECT
        t.course_id,
        lo.lo_symbol
    FROM learning_outcome lo
    JOIN topics t ON lo.topic_id = t.topic_id
    WHERE t.course_id IS NOT NULL
),
lo_counts AS (
    SELECT
        course_id,
        lo_symbol,
        COUNT(*) AS occurrence_count
    FROM lo_data
    GROUP BY course_id, lo_symbol
),
course_totals AS (
    SELECT
        course_id,
        SUM(occurrence_count) AS total_occurrences
    FROM lo_counts
    GROUP BY course_id
)
SELECT
    lc.course_id,
    lc.lo_symbol,
    ROUND(lc.occurrence_count / ct.total_occurrences, 2) AS weight
FROM lo_counts lc
JOIN course_totals ct ON lc.course_id = ct.course_id
ORDER BY lc.course_id, lc.lo_symbol;
