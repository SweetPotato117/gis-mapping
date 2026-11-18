<?php
// Keep existing bookmarks pointing to the dashboard editor.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
header('Location: admin.php' . ($id ? '?edit=' . $id : ''));
exit;
