-- Count the total number of distinct lo_symbols
SELECT COUNT(DISTINCT lo_symbol) AS total_symbols
FROM learning_outcome;

-- List all distinct lo_symbols and count how many times each appears
SELECT 
    lo_symbol, 
    COUNT(*) AS occurrence_count
FROM learning_outcome
GROUP BY lo_symbol
ORDER BY lo_symbol;
