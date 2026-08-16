private function getNotifications()
{
    ...
    SELECT
        id,
        type,
        title,
        message,
        link,
        is_read,
        created_at
    FROM notifications
    ...
}