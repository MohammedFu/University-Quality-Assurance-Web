-- Check if learning_outcome table has data
SELECT COUNT(*) AS record_count FROM learning_outcome;

-- If empty, we need to populate it with sample data
-- Here's a script to add sample learning outcomes for each topic

-- First, let's see what topics we have
SELECT * FROM topics LIMIT 10;
