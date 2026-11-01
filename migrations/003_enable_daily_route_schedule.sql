-- Add a daily active schedule for Route 1 to support collector testing.
INSERT INTO schedule (route_id, day_name, collection_time, status)
SELECT 1, days.day_name, '8:00 AM - 11:00 AM', 'active'
FROM (
    SELECT 'Monday' AS day_name
    UNION ALL SELECT 'Tuesday'
    UNION ALL SELECT 'Wednesday'
    UNION ALL SELECT 'Thursday'
    UNION ALL SELECT 'Friday'
    UNION ALL SELECT 'Saturday'
    UNION ALL SELECT 'Sunday'
) AS days
WHERE NOT EXISTS (
    SELECT 1
    FROM schedule s
    WHERE s.route_id = 1
      AND s.day_name = days.day_name
      AND s.status = 'active'
);
