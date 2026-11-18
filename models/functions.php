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

function normalizePlaceSearch(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, ['é' => 'e', 'ñ' => 'n', '’' => '', "'" => '']);
    $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? '';
    $text = preg_replace('/\bwi\s*fi\b/u', 'wifi', $text);
    return trim($text);
}

function searchLocations(string $searchTerm): array
{
    global $conn;
    require_once __DIR__ . '/place_about.php';

    $query = normalizePlaceSearch($searchTerm);
    if ($query === '') return [];

    // Keep qualified tags together so free Wi-Fi and paid parking cannot match "free parking".
    preg_match_all('/\b(?:free (?:wifi|parking|breakfast|charging)|no (?:pets|smoking|outside food)|smoke free)\b|[\p{L}\p{N}]+/u', $query, $parts);
    $ignore = ['with', 'and', 'near', 'that', 'has', 'have', 'a', 'the', 'places'];
    $plural = ['restaurants' => 'restaurant', 'hotels' => 'hotel', 'cafes' => 'cafe', 'restrooms' => 'restroom'];
    $terms = array_values(array_unique(array_map(
        static fn ($term) => $plural[$term] ?? $term,
        array_diff($parts[0], $ignore)
    )));
    if (!$terms) $terms = [$query];

    $aliases = [
        'accessible' => 'wheelchair step free pwd',
        'takeout' => 'take out takeaway take away',
        'restroom' => 'bathroom toilet comfort room cr',
        'gender_neutral_restroom' => 'all gender bathroom',
        'reservations' => 'booking reserve table',
        'charging' => 'free phone charging',
        'air_conditioning' => 'air conditioning aircon ac',
        'fitness_center' => 'gym fitness centre',
        'no_pets' => 'no pets pet not allowed',
        'no_smoking' => 'no smoking non smoking',
        'no_outside_food' => 'no outside food',
    ];
    $tagIndex = [];
    foreach (PLACE_ABOUT_GROUPS as $group => $catalog) {
        foreach ($catalog['options'] as $key => [$label, $icon]) {
            $tagIndex[$group][$key] = [
                'label' => $label,
                'icon' => $icon,
                'search' => ' ' . normalizePlaceSearch($label . ' ' . ($aliases[$key] ?? ''))
            ];
        }
    }

    $menus = [];
    foreach ($conn->query('SELECT place_id, name, type FROM menu ORDER BY id') as $item) {
        $menus[$item['place_id']][] = $item;
    }

    $results = [];
    $places = $conn->query('SELECT id, name, type, address, description, lat, lng, contact, open_hours, place_about FROM places');
    foreach ($places as $place) {
        $name = normalizePlaceSearch($place['name'] ?? '');
        $metadata = normalizePlaceSearch(implode(' ', [$place['type'], $place['address'], $place['description']]));
        $about = decodePlaceAbout($place['place_about']);
        $remaining = array_fill_keys($terms, true);
        foreach ($terms as $term) {
            if (str_contains($name, $term) || str_contains($metadata, $term)) unset($remaining[$term]);
        }

        $matchedMenu = null;
        $menu = $menus[$place['id']] ?? [];
        foreach ($menu as $item) {
            $menuText = normalizePlaceSearch($item['name'] . ' ' . $item['type']);
            foreach ($terms as $term) {
                if (!str_contains($menuText, $term)) continue;
                unset($remaining[$term]);
                $matchedMenu ??= $item['name'];
            }
        }

        $matchedTags = [];
        $tagTerms = [];
        foreach ($about as $group => $options) {
            foreach ($options as $key) {
                $tag = $tagIndex[$group][$key];
                $matches = false;
                foreach ($terms as $term) {
                    if (!str_contains($tag['search'], ' ' . $term)) continue;
                    unset($remaining[$term]);
                    $tagTerms[$term] = true;
                    $matches = true;
                }
                if ($matches) $matchedTags[] = ['label' => $tag['label'], 'icon' => $tag['icon']];
            }
        }
        if ($remaining) continue;

        $nameScore = $name === $query ? 100 : (str_starts_with($name, $query) ? 70 : (str_contains($name, $query) ? 45 : 0));
        $menuScore = $matchedMenu && str_contains(normalizePlaceSearch($matchedMenu), $query) ? 20 : 0;
        $place['id'] = (int)$place['id'];
        $place['place_about'] = $about;
        $place['menu_items'] = $menu ? implode(', ', array_unique(array_column($menu, 'name'))) : null;
        $place['matched_menu'] = $matchedMenu;
        $place['matched_tags'] = $matchedTags;
        $place['relevance'] = $nameScore + $menuScore + 8 * count($tagTerms);
        $results[] = $place;
    }

    usort($results, static fn ($a, $b) =>
        ($b['relevance'] <=> $a['relevance']) ?: strcasecmp($a['name'], $b['name']) ?: ($a['id'] <=> $b['id'])
    );
    return array_slice($results, 0, 20);
}
