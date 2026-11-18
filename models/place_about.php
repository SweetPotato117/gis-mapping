<?php

const PLACE_ABOUT_GROUPS = [
    'amenities' => [
        'title' => 'Amenities',
        'options' => [
            'wifi' => ['Free Wi-Fi', 'wifi'],
            'breakfast' => ['Free breakfast', 'breakfast'],
            'parking' => ['Free parking', 'car'],
            'accessible' => ['Accessible', 'accessible'],
            'pool' => ['Outdoor pool', 'pool'],
            'air_conditioning' => ['Air-conditioned', 'air-conditioning'],
            'laundry' => ['Laundry service', 'laundry'],
            'room_service' => ['Room service', 'room-service'],
            'kid_friendly' => ['Kid-friendly', 'children'],
            'restaurant' => ['Restaurant', 'utensils'],
            'airport_shuttle' => ['Airport shuttle', 'shuttle'],
            'fitness_center' => ['Fitness center', 'fitness'],
            'bar' => ['Bar', 'bar'],
            'wifi_available' => ['Wi-Fi available', 'wifi'],
            'breakfast_available' => ['Breakfast available', 'breakfast'],
            'parking_available' => ['Parking available', 'car'],
            'swimming_pool' => ['Swimming pool', 'pool'],
            'indoor_pool' => ['Indoor pool', 'pool'],
            'dine_in' => ['Dine-in', 'utensils'],
            'takeout' => ['Takeout', 'takeout'],
            'delivery' => ['Delivery', 'delivery'],
            'reservations' => ['Online reservations', 'calendar-check'],
            'private_dining' => ['Private dining room', 'room-service'],
            'restroom' => ['Restroom', 'restroom'],
            'gender_neutral_restroom' => ['Gender-neutral restroom', 'restroom'],
            'charging' => ['Free charging', 'charging'],
            'pet_friendly' => ['Pet-friendly', 'paw'],
            'non_smoking_rooms' => ['Non-smoking rooms', 'no-smoking'],
            'smoking_area' => ['Designated smoking area', 'smoking-area'],
            'smart_tv' => ['Smart TV', 'television'],
            'in_room_safe' => ['In-room safe', 'room-safe'],
            'refrigerator' => ['Refrigerator in select rooms', 'refrigerator'],
            'drinking_water' => ['Filtered drinking water', 'water-drop'],
            'karaoke' => ['Karaoke', 'microphone'],
            'cafe' => ['Café', 'coffee-cup'],
            'outdoor_seating' => ['Outdoor seating', 'outdoor-table'],
            'event_space' => ['Event space', 'calendar-check'],
            'security' => ['24-hour security', 'security-shield'],
            'business_center' => ['Business center', 'briefcase'],
            'spa' => ['Spa', 'spa'],
            'playground' => ['Playground', 'children'],
            'bonsai_garden' => ['Bonsai garden', 'bonsai'],
            'buffet' => ['All-you-can-eat dining', 'breakfast'],
            'farm_stay' => ['Farm stay', 'farm-stay'],
            'cultural_exhibits' => ['Cultural exhibits', 'museum'],
            'mushroom_products' => ['Mushroom products', 'mushroom'],
            'museum' => ['Museum', 'museum'],
            'horseback_riding' => ['Horseback riding', 'horseback'],
            'cycling' => ['Cycling', 'bicycle'],
            'photography' => ['Photo shoots', 'camera'],
        ],
    ],
    'house_rules' => [
        'title' => 'House rules',
        'options' => [
            'no_smoking' => ['Smoke-free', 'no-smoking'],
            'no_pets' => ['Pets not allowed', 'no-pets'],
            'no_outside_food' => ['No outside food or drinks', 'no-outside-food'],
        ],
    ],
];

function decodePlaceAbout(?string $json): array
{
    $stored = json_decode($json ?? '', true);
    $about = [];
    foreach (PLACE_ABOUT_GROUPS as $key => $group) {
        $selection = $stored[$key] ?? [];
        $about[$key] = array_values(array_filter(
            array_keys($group['options']),
            static fn ($option) => is_array($selection) && in_array($option, $selection, true)
        ));
    }
    return $about;
}

function placeAboutWriteFields(array $request): array
{
    if (!array_key_exists('place_about', $request)) {
        return [];
    }
    $json = $request['place_about'];
    if (!is_string($json) || !is_array(json_decode($json, true))) {
        throw new InvalidArgumentException('Invalid amenities or house rules.');
    }
    return ['place_about' => json_encode(decodePlaceAbout($json), JSON_THROW_ON_ERROR)];
}
