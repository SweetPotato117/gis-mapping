<?php
include 'dbconn.php';

function insertRecord($table, $data)
{
    global $conn;
    
    $columns = array_keys($data);
    $values = array_values($data);
    
    // Properly escape and quote values
    $escaped_values = array_map(function($value) use ($conn) {
        if (is_numeric($value)) {
            return $value;
        }
        return "'" . mysqli_real_escape_string($conn, $value) . "'";
    }, $values);
    
    $columns_str = implode(", ", $columns);
    $values_str = implode(", ", $escaped_values);
    
    $query = "INSERT INTO $table ($columns_str) VALUES ($values_str)";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Insert Error: " . mysqli_error($conn));
        error_log("Query: " . $query);
    }
    
    return $result;
}

// Keep all your other existing functions...
function editRecord($table, $data, $condition)
{
    global $conn;
    $updateData = [];
    foreach ($data as $column => $value) {
        $updateData[] = "$column = '$value'";
    }
    $updateString = implode(", ", $updateData);
    $query = "UPDATE $table SET $updateString WHERE $condition";
    return mysqli_query($conn, $query);
}

function deleteRecord($table, $condition)
{
    global $conn;
    $query = "DELETE FROM $table WHERE $condition";
    return mysqli_query($conn, $query);
}

function getAllRecords($table, $condition = '')
{
    global $conn;
    $query = "SELECT * FROM $table $condition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getRecord($table, $condition)
{
    global $conn;
    $query = "SELECT * FROM $table WHERE $condition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function getRecordMultiTable($table1, $table2, $onCondition, $whereCondition)
{
    global $conn;
    $query = "SELECT * FROM $table1 LEFT JOIN $table2 ON $onCondition WHERE $whereCondition";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function countAllRecords($table, $whereCondition = '1')
{
    global $conn;
    $query = "SELECT COUNT(*) as total FROM $table WHERE $whereCondition";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

function updateRecord($table, $data, $condition)
{
    global $conn;

    $updates = [];
    foreach ($data as $key => $value) {
        if ($value === null || $value === 'NULL') {
            $updates[] = "$key = NULL";
        } else {
            $updates[] = "$key = '" . mysqli_real_escape_string($conn, $value) . "'";
        }
    }

    $sql = "UPDATE $table SET " . implode(', ', $updates) . " WHERE $condition";
    return mysqli_query($conn, $sql);
}

function startTransaction()
{
    global $conn;
    mysqli_autocommit($conn, false);
    mysqli_begin_transaction($conn);
}

function commitTransaction()
{
    global $conn;
    mysqli_commit($conn);
    mysqli_autocommit($conn, true);
}

function rollbackTransaction()
{
    global $conn;
    mysqli_rollback($conn);
    mysqli_autocommit($conn, true);
}

function hideNameGCashStyle($firstName, $lastName)
{
    $hiddenParts = [];

    if (!empty($firstName)) {
        $firstNames = explode(' ', trim($firstName));

        foreach ($firstNames as $name) {
            if (empty($name)) {
                continue;
            }

            $length = strlen($name);

            if ($length <= 2) {
                $hiddenParts[] = $name[0] . str_repeat('*', $length - 1);
            } else {
                $hiddenParts[] = $name[0] . str_repeat('*', $length - 2) . $name[$length - 1];
            }
        }
    }

    if (!empty($lastName)) {
        $hiddenParts[] = $lastName[0] . '.';
    }

    return implode(' ', $hiddenParts);
}

function getAllPlaces() {
    global $conn;
    $query = "SELECT * FROM places";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return false;
    }
    
    $places = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $places[$row['id']] = $row;
    }
    
    return $places;
}

function getPlace($id) {
    global $conn;
    $query = "SELECT * FROM places WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return false;
    }
    
    $places = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $places[$row['id']] = $row;
    }
    
    return $places;
}




function getPlaceMenu($placeId) {
    global $conn;
    $placeId = (int)$placeId;
    $query = "SELECT name, price, id FROM menu WHERE place_id = $placeId";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPlaceHighlights($placeId) {
    global $conn;
    $placeId = (int)$placeId;
    $query = "SELECT image_url, id FROM highlights WHERE place_id = $placeId";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getPlaceWebsites($placeId) {
    global $conn;
    $placeId = (int)$placeId;
    $query = "SELECT link, id FROM website_url WHERE place_id = $placeId";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


//search item 

function searchLocations($searchTerm) {
    global $conn;
    
    // Escape search term
    $searchTerm = mysqli_real_escape_string($conn, $searchTerm);
    $likeTerm = "%{$searchTerm}%";
    
    // Search in places and menu with relevance scoring
    $query = "SELECT DISTINCT 
                p.id,
                p.name,
                p.type,
                p.address,
                p.description,
                p.lat,
                p.lng,
                p.contact,
                p.open_hours,
                GROUP_CONCAT(DISTINCT m.name SEPARATOR ', ') as menu_items,
                (
                    CASE 
                        WHEN p.name LIKE '$searchTerm%' THEN 10
                        WHEN p.name LIKE '$likeTerm' THEN 5
                        WHEN m.name LIKE '$searchTerm%' THEN 8
                        WHEN m.name LIKE '$likeTerm' THEN 4
                        ELSE 1
                    END
                ) as relevance
            FROM places p
            LEFT JOIN menu m ON p.id = m.place_id
            WHERE 
                p.name LIKE '$likeTerm' OR
                p.address LIKE '$likeTerm' OR
                p.description LIKE '$likeTerm' OR
                p.type LIKE '$likeTerm' OR
                m.name LIKE '$likeTerm' OR
                m.type LIKE '$likeTerm'
            GROUP BY p.id
            ORDER BY relevance DESC, p.name ASC
            LIMIT 20";
    
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        return [];
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


?>